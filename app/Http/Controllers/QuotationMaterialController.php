<?php

namespace App\Http\Controllers;

use App\Models\QuotationMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationMaterialController extends Controller
{
    /**
     * Attach selected existing materials to a quotation
     */
    public function storeSelected(Request $request)
    {
        $data = $request->validate([
            'quot_id'   => 'required|integer|exists:quotations,id',
            'selected'  => 'required|array',
            'quantity'  => 'array', // quantities keyed by material id
        ]);

        $quotation = \App\Models\Quotation::with(['materials'])->findOrFail($data['quot_id']);   
        $selected = $data['selected'];
        $quantities = $request->input('quantity', []);

        foreach ($selected as $matId) {
            $qty = isset($quantities[$matId]) ? (int) $quantities[$matId] : 1;
            if ($qty < 1) $qty = 1;

            $existing = $quotation->materials()->wherePivot('material_id', $matId)->first();     
            if ($existing) {
                $newQty = ($existing->pivot->quantity ?? 0) + $qty;
                $quotation->materials()->updateExistingPivot($matId, [
                    'quantity'  => $newQty,
                    'unit_cost' => $existing->unit_price, // or fetch fresh from Material        
                ]);
            } else {
                $material = \App\Models\Material::find($matId);
                $quotation->materials()->attach($matId, [
                    'quantity'  => $qty,
                    'unit_cost' => $material ? $material->unit_price : 0,
                ]);
            }
        }

        $quotation->load('materials');

        $materials = $quotation->materials->map(function ($m) {
            return [
                'id'         => $m->id,
                'name'       => $m->name,
                'unit'       => $m->unit,
                'unit_price' => (float) $m->pivot->unit_cost,
                'quantity'   => (int) ($m->pivot->quantity ?? 0),
                'line_total' => (float) ($m->pivot->unit_cost * ($m->pivot->quantity ?? 0)),     
                'pivot_id'   => $m->pivot->id ?? null,
            ];
        })->values();

        $materialsSubtotal = $materials->sum('line_total');
        $labor = (float) ($quotation->labor_fee ?? 0);
        $delivery = (float) ($quotation->delivery_fee ?? 0);
        $grandTotal = $materialsSubtotal + $labor + $delivery;

        return response()->json([
            'success'      => true,
            'message'      => 'Materials added/updated on quotation',
            'materials'    => $materials,
            'subtotal'     => $materialsSubtotal,
            'labor_fee'    => $labor,
            'delivery_fee' => $delivery,
            'grand_total'  => $grandTotal,
        ]);
    }

    /**
     * Store a material directly for a quotation
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'quot_id'    => 'required|integer|exists:quotations,id',
            'material_id' => 'required|integer|exists:materials,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        try {
            $quotation = \App\Models\Quotation::findOrFail($data['quot_id']);
            $material = \App\Models\Material::findOrFail($data['material_id']);

            $quotation->materials()->attach($material->id, [
                'quantity'  => $data['quantity'],
                'unit_cost' => $material->unit_price,
            ]);

            $quotation->load('materials');

            $materials = $quotation->materials->map(function ($m) {
                return [
                    'id'         => $m->id,
                    'name'       => $m->name,
                    'unit'       => $m->unit,
                    'unit_price' => (float) $m->pivot->unit_cost,
                    'quantity'   => (int) ($m->pivot->quantity ?? 0),
                    'line_total' => (float) ($m->pivot->unit_cost * ($m->pivot->quantity ?? 0)),
                    'pivot_id'   => $m->pivot->id ?? null,
                ];
            })->values();

            $grandTotal = $materials->sum('line_total') + $quotation->labor_fee + $quotation->delivery_fee;

            return response()->json([
                'success'     => true,
                'message'     => 'Material added to quotation',
                'materials'   => $materials,
                'grand_total' => $grandTotal,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding material: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a material from quotation (via pivot ID)
     */
    public function destroy($pivotId)
    {
        $pivot = DB::table('quotation_materials')->where('id', $pivotId)->first();

        if (!$pivot) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found in quotation.'
            ], 404);
        }

        DB::table('quotation_materials')->where('id', $pivotId)->delete();

        $quotation = \App\Models\Quotation::with('materials')->find($pivot->quotation_id);       
        $grandTotal = $quotation->materials->sum(function ($m) {
            return $m->pivot->unit_cost * $m->pivot->quantity;
        }) + $quotation->labor_fee + $quotation->delivery_fee;

        return response()->json([
            'success'     => true,
            'message'     => 'Material deleted successfully.',
            'grand_total' => $grandTotal
        ]);
    }

    /**
     * Update material quantity in a quotation
     */
    public function updateQuantity(Request $request)
    {
        $request->validate([
            'pivot_id' => 'required|integer',
            'quot_id'  => 'required|integer',
            'quantity' => 'required|numeric|min:1',
        ]);

        $quotation = \App\Models\Quotation::with('materials')->findOrFail($request->quot_id);        
        $material = $quotation->materials()->wherePivot('id', $request->pivot_id)->first();

        if (!$material) {
            return response()->json(['success' => false, 'message' => 'Material not found in quotation.']);
        }

        $quotation->materials()->updateExistingPivot($material->id, [
            'quantity' => $request->quantity,
        ]);

        // reload to get updated pivot
        $quotation->load('materials');

        // calculate new line total and grand total
        $lineTotal = $material->pivot->unit_cost * $request->quantity;
        $materialsSubtotal = $quotation->materials->sum(fn($m) => $m->pivot->unit_cost * $m->pivot->quantity);
        $grandTotal = $materialsSubtotal + $quotation->labor_fee + $quotation->delivery_fee;

        return response()->json([
            'success'        => true,
            'message'        => 'Quantity updated successfully.',
            'line_total'     => $lineTotal,
            'materials_total'=> $materialsSubtotal,
            'grand_total'    => $grandTotal,
            'labor_fee'      => $quotation->labor_fee,
            'delivery_fee'   => $quotation->delivery_fee,
        ]);
    }

    /**
     * Update the unit_cost of a quotation material (AJAX).
     */
    public function updateUnitCost(Request $request, $pivotId)
    {
        try {
            $pivot = QuotationMaterial::find($pivotId);
            if (!$pivot) {
                return response()->json(['success' => false, 'message' => 'Quotation material not found.'], 404);
            }
            try {
                $validated = $request->validate([
                    'unit_cost' => 'required|numeric|min:0',
                ]);
            } catch (\Illuminate\Validation\ValidationException $ve) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $ve->errors(),
                ], 422);
            }
            $pivot->unit_cost = $validated['unit_cost'];
            $pivot->save();

            // Also update the material's unit_price to match
            $material = \App\Models\Material::find($pivot->material_id);
            if ($material) {
                $material->unit_price = $validated['unit_cost'];
                $material->save();
            }

            // Recalculate grand total for the quotation
            $quotation = \App\Models\Quotation::with('materials')->find($pivot->quotation_id);
            $grandTotal = 0;
            if ($quotation) {
                $grandTotal = $quotation->materials->sum(function ($m) {
                    return $m->pivot->unit_cost * $m->pivot->quantity;
                }) + ($quotation->labor_fee ?? 0) + ($quotation->delivery_fee ?? 0);
            }

            return response()->json([
                'success'     => true,
                'message'     => 'Unit cost updated.',
                'pivot'       => $pivot,
                'grand_total' => $grandTotal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new material and attach to quotation
     */
    public function createMaterialAndAttach(Request $request)
    {
        $data = $request->validate([
            'quot_id'    => 'required|integer|exists:quotations,id',
            'name'       => 'required|string|max:255',
            'unit'       => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'quantity'   => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $material = \App\Models\Material::create([
                'name'       => $data['name'],
                'unit'       => $data['unit'],
                'unit_price' => $data['unit_price'],
            ]);

            $quotation = \App\Models\Quotation::findOrFail($data['quot_id']);
            $quotation->materials()->attach($material->id, [
                'quantity'  => $data['quantity'],
                'unit_cost' => $data['unit_price'],
            ]);

            DB::commit();

            $quotation->load('materials');

            $materials = $quotation->materials->map(function ($m) {
                return [
                    'id'         => $m->id,
                    'name'       => $m->name,
                    'unit'       => $m->unit,
                    'unit_price' => (float) $m->pivot->unit_cost,
                    'quantity'   => (int) ($m->pivot->quantity ?? 0),
                    'line_total' => (float) ($m->pivot->unit_cost * ($m->pivot->quantity ?? 0)), 
                    'pivot_id'   => $m->pivot->id ?? null,
                ];
            })->values();

            $materialsSubtotal = $materials->sum('line_total');
            $labor = (float) ($quotation->labor_fee ?? 0);
            $delivery = (float) ($quotation->delivery_fee ?? 0);
            $grandTotal = $materialsSubtotal + $labor + $delivery;

            return response()->json([
                'success'      => true,
                'message'      => 'New material created and added to quotation',
                'materials'    => $materials,
                'subtotal'     => $materialsSubtotal,
                'labor_fee'    => $labor,
                'delivery_fee' => $delivery,
                'grand_total'  => $grandTotal,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create material and attach',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}



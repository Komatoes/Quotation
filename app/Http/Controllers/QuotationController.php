<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Material;
use App\Models\QuotationMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function store(Request $request)
    {
        // Create client
        $client = \App\Models\Client::create([
            'first_name'  => $request->client_first_name,
            'last_name'   => $request->client_last_name,
            'contact_no'  => $request->client_contact_no,
            'address'     => $request->client_address,
        ]);

        // Create quotation
        $quotation = \App\Models\Quotation::create([
            'subject'      => $request->subject,
            'description'  => $request->description ?? '',
            'employee_id'  => auth()->id(),
            'client_id'    => $client->id,
            'status_id'    => 4, // Ongoing by default
            'labor_fee'    => $request->labor_fee ?? 0,
            'delivery_fee' => $request->delivery_fee ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quotation created successfully!',
            'quotation' => $quotation,
            'client' => $client
        ]);
    }

    public function show($id)
    {
        $quotation = Quotation::findOrFail($id);
        $client = \App\Models\Client::findOrFail($quotation->client_id);
        $materials = $quotation->materials; // Many-to-many via quotation_materials
        return view('quotation', compact('quotation', 'client', 'materials'));
    }

    /**
     * Store selected materials into quotation_materials table
     */
    public function addMaterials(Request $request)
    {
        $data = $request->validate([
            'quot_id'   => 'required|integer|exists:quotations,id',
            'selected'  => 'required|array',
            'quantity'  => 'array', // quantities keyed by material id
        ]);

        $quotation = Quotation::with(['materials'])->findOrFail($data['quot_id']);
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
                $material = Material::find($matId);
                $quotation->materials()->attach($matId, [
                    'quantity'  => $qty,
                    'unit_cost' => $material ? $material->unit_price : 0,
                ]);
            }
        }

        // reload relation
        $quotation->load('materials');

        // build response data
        $materials = $quotation->materials->map(function ($m) {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'unit' => $m->unit,
                'unit_price' => (float) $m->pivot->unit_cost, // ✅ keep historical price
                'quantity' => (int) ($m->pivot->quantity ?? 0),
                'line_total' => (float) ($m->pivot->unit_cost * ($m->pivot->quantity ?? 0)), // ✅ correct total
                'pivot_id' => $m->pivot->id ?? null,
            ];
        })->values();

        $materialsSubtotal = $materials->sum('line_total');
        $labor = (float) ($quotation->labor_fee ?? 0);
        $delivery = (float) ($quotation->delivery_fee ?? 0);
        $grandTotal = $materialsSubtotal + $labor + $delivery;

        return response()->json([
            'success' => true,
            'message' => 'Materials added/updated on quotation',
            'materials' => $materials,
            'subtotal' => $materialsSubtotal,
            'labor_fee' => $labor,
            'delivery_fee' => $delivery,
            'grand_total' => $grandTotal,
        ]);
    }

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

        // Optionally recalc grand total
        $quotation = Quotation::with('materials')->find($pivot->quotation_id);
        $grandTotal = $quotation->materials->sum(function ($m) {
            return $m->pivot->unit_cost * $m->pivot->quantity;
        }) + $quotation->labor_fee + $quotation->delivery_fee;

        return response()->json([
            'success' => true,
            'message' => 'Material deleted successfully.',
            'grand_total' => $grandTotal
        ]);
    }
}

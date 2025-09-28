<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Material;
use App\Models\QuotationMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function viewHome()
    {
        return view('dashboard');
    }
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
            'employee_id'  => 1,
            'client_id'    => $client->id,
            'status_id'    => 1, // default status
            'labor_fee'    => $request->labor_fee ?? 0,
            'delivery_fee' => $request->delivery_fee ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'quotation_id' => $quotation->id,
            'client_id' => $client->id,
        ]);
    }

    public function rejected()
    {
        $rejected = Quotation::with(['client', 'employee', 'status'])
            ->where('status_id', 3) // Rejected
            ->get();

        return response()->json($rejected);
    }

    public function createMaterialAndAttach(Request $request)
    {
        $data = $request->validate([
            'quot_id'   => 'required|integer|exists:quotations,id',
            'name'      => 'required|string|max:255',
            'unit'      => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'quantity'  => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // 1. Create the new material in the DB
            $material = Material::create([
                'name'       => $data['name'],
                'unit'       => $data['unit'],
                'unit_price' => $data['unit_price'],
            ]);

            // 2. Attach it to the quotation
            $quotation = Quotation::findOrFail($data['quot_id']);
            $quotation->materials()->attach($material->id, [
                'quantity'  => $data['quantity'],
                'unit_cost' => $data['unit_price'], // historical price
            ]);

            DB::commit();

            // reload relation
            $quotation->load('materials');

            // prepare response
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
                'success' => true,
                'message' => 'New material created and added to quotation',
                'material' => $material,
                'materials' => $materials,
                'subtotal' => $materialsSubtotal,
                'labor_fee' => $labor,
                'delivery_fee' => $delivery,
                'grand_total' => $grandTotal,
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
    public function show($id)
    {
        $quotation = Quotation::findOrFail($id);
        $client = \App\Models\Client::findOrFail($quotation->client_id);
        $materials = $quotation->materials; // Many-to-many via quotation_materials
        return view('quotation', compact('quotation', 'client', 'materials'));
    }
}

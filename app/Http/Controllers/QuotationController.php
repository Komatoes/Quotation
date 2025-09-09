<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Material;
use App\Models\QuotationMaterial;
use Illuminate\Http\Request;

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
        $quotationId = $request->input('quot_id');
        $selectedMaterials = $request->input('selected', []);
        $quantities = $request->input('quantity', []);

        if (!$quotationId || empty($selectedMaterials)) {
            return response()->json([
                'success' => false,
                'message' => 'No materials selected or invalid quotation.'
            ]);
        }

        foreach ($selectedMaterials as $materialId) {
            $quantityToAdd = $quantities[$materialId] ?? 1;

            $material = \App\Models\Material::find($materialId);
            if (!$material) continue;

            // Check if material already exists in the quotation
            $existing = \App\Models\QuotationMaterial::where('quotation_id', $quotationId)
                ->where('material_id', $materialId)
                ->first();

            if ($existing) {
                // Update quantity
                $existing->quantity += $quantityToAdd;
                $existing->unit_cost = $material->unit_price; // ensure latest cost
                $existing->save();
            } else {
                // Insert new row
                \App\Models\QuotationMaterial::create([
                    'quotation_id' => $quotationId,
                    'material_id'  => $materialId,
                    'quantity'     => $quantityToAdd,
                    'unit_cost'    => $material->unit_price,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Materials added to quotation successfully.'
        ]);
    }
}

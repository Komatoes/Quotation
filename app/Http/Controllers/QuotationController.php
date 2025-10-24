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
    public function viewReport($id)
    {
        $quotation = Quotation::with(['client', 'employee', 'materials'])->findOrFail($id);
        return view('view-report', compact('quotation'));
    }
    public function viewdraft($id)
    {
        $quotation = Quotation::with(['client', 'employee', 'materials'])->findOrFail($id);
        return view('view-draft', compact('quotation'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_first_name' => 'required|string|max:255',
            'client_last_name'  => 'required|string|max:255',
            'client_contact_no' => 'required|digits:11',
            'client_address'    => 'required|string|max:255',
            'subject'           => 'required|string|max:255',
            'description'       => 'required|string|max:1000',
            'labor_fee'         => 'nullable|numeric|min:0',
            'delivery_fee'      => 'nullable|numeric|min:0',
        ]);

        $client = \App\Models\Client::create([
            'first_name'  => $validated['client_first_name'],
            'last_name'   => $validated['client_last_name'],
            'contact_no'  => $validated['client_contact_no'],
            'address'     => $validated['client_address'],
        ]);

        $quotation = \App\Models\Quotation::create([
            'subject'      => $validated['subject'],
            'description'  => $validated['description'],
            'employee_id'  => 1, // or auth()->id()
            'client_id'    => $client->id,
            'status_id'    => 1,
            'labor_fee'    => $validated['labor_fee'] ?? 0,
            'delivery_fee' => $validated['delivery_fee'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'quotation_id' => $quotation->id,
            'client_id' => $client->id,
            'message' => 'Quotation created successfully!',
        ]);
    }


    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status_id' => 'required|in:1,2,3', // only allow Draft, Approved, Rejected
        ]);

        $quotation = Quotation::findOrFail($id);
        $quotation->status_id = $validated['status_id'];
        $quotation->save();

        return response()->json([
            'success' => true,
            'message' => 'Quotation status updated successfully!',
            'quotation' => $quotation
        ]);
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
    public function updateFee(Request $request, $id)
    {
        //  Validate request
        $validated = $request->validate([
            'field' => 'required|in:labor_fee,delivery_fee',
            'value' => 'required|numeric|min:0'
        ]);

        //  Find quotation
        $quotation = Quotation::with('materials')->findOrFail($id);

        //  Update the requested fee (labor_fee or delivery_fee)
        $quotation->{$validated['field']} = $validated['value'];
        $quotation->save();

        //  Recalculate totals
        $materialsTotal = $quotation->materials->sum(
            fn($m) => $m->pivot->unit_cost * $m->pivot->quantity
        );

        $grandTotal = $materialsTotal + $quotation->labor_fee + $quotation->delivery_fee;

        return response()->json([
            'success'       => true,
            'message'       => ucfirst(str_replace('_', ' ', $validated['field'])) . ' updated successfully',
            'materials_total' => $materialsTotal,
            'labor_fee'     => $quotation->labor_fee,
            'delivery_fee'  => $quotation->delivery_fee,
            'grand_total'   => $grandTotal,
        ]);
    }

    public function drafts()
    {
        $drafts = Quotation::with(['client', 'employee', 'status'])
            ->where('status_id', 1) // Draft
            ->get();

        return response()->json($drafts);
    }

    public function approved()
    {
        $approved = Quotation::with(['client', 'employee', 'status'])
            ->where('status_id', 2) // Approved
            ->get();

        return response()->json($approved);
    }

    public function rejected()
    {
        $rejected = Quotation::with(['client', 'employee', 'status'])
            ->where('status_id', 3) // Rejected
            ->get();

        return response()->json($rejected);
    }
    public function getMaterials($id)
    {
        $quotation = \App\Models\Quotation::with('materials')->findOrFail($id);

        $materials = $quotation->materials->map(function ($m) {
            return [
                'pivot_id'   => $m->pivot->id,
                'name'       => $m->name,
                'unit'       => $m->unit,
                'unit_price' => (float) $m->pivot->unit_cost,
                'quantity'   => (int) $m->pivot->quantity,
                'line_total' => (float) ($m->pivot->unit_cost * $m->pivot->quantity),
            ];
        });

        $grandTotal = $materials->sum('line_total') + ($quotation->labor_fee ?? 0) + ($quotation->delivery_fee ?? 0);

        return response()->json([
            'success' => true,
            'materials' => $materials,
            'grand_total' => $grandTotal
        ]);
    }
    public function markCompleted($id)
    {
        $quotation = Quotation::findOrFail($id);
        $completedStatus = DB::table('quotation_status')->where('status_name', 'Completed')->first();

        if ($completedStatus) {
            $quotation->status_id = $completedStatus->id;
            $quotation->save();

            return response()->json(['message' => 'Quotation marked as completed successfully.']);
        }

        return response()->json(['message' => 'Completed status not found.'], 400);
    }
    public function createRevision(Request $request, $id)
    {
        $quotation = Quotation::with(['client', 'materials'])->findOrFail($id);

        // Optional: reason for revision
        $reason = $request->input('reason', 'No reason provided');

        // Store the old data as JSON
        $revisionData = [
            'subject'      => $quotation->subject,
            'description'  => $quotation->description,
            'labor_fee'    => $quotation->labor_fee,
            'delivery_fee' => $quotation->delivery_fee,
            'status_id'    => $quotation->status_id,
            'materials'    => $quotation->materials->map(function ($m) {
                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'unit' => $m->unit,
                    'unit_price' => $m->pivot->unit_cost,
                    'quantity' => $m->pivot->quantity,
                ];
            }),
        ];

        // Create revision record
        DB::table('quotation_revisions')->insert([
            'quotation_id' => $quotation->id,
            'old_data'     => json_encode($revisionData),
            'reason'       => $reason,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Optional: reset quotation to Draft for editing
        $quotation->status_id = 1; // Draft
        $quotation->save();

        return response()->json([
            'success' => true,
            'message' => 'Quotation revision created successfully.',
            'quotation_id' => $quotation->id
        ]);
    }
}

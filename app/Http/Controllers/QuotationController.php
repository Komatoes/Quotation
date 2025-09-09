<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
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
            'employee_id'  => auth()->id(), // currently logged-in employee
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
        // Fetch quotation by ID
        $quotation = \App\Models\Quotation::findOrFail($id);

        // Fetch the client related to this quotation
        $client = \App\Models\Client::findOrFail($quotation->client_id);

        // Fetch related materials (assuming many-to-many relationship via quotation_materials)
        $materials = $quotation->materials; // or use ->with('materials') if defined in model

        // Pass all data to the view
        return view('quotation', compact('quotation', 'client', 'materials'));
    }
}

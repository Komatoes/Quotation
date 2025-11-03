<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CustomerInteraction;
use App\Models\ServiceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view customers|manage customers']);
    }

    /**
     * Link an existing quotation to a customer
     */
    

    /**
     * Link an existing quotation to a customer
     */
    public function linkQuotation(Request $request, Client $client)
    {
        $validated = $request->validate([
            'quotation_id' => 'required|exists:quotations,id'
        ]);

        $quotation = Quotation::findOrFail($validated['quotation_id']);
        $quotation->client_id = $client->id;
        $quotation->save();

        return response()->json([
            'message' => 'Quotation linked successfully',
            'quotation' => $quotation
        ]);
    }

    /**
     * Get all quotations for a customer
     */
    public function getQuotations(Client $client)
    {
        $quotations = $client->quotations()
            ->with(['status', 'materials'])
            ->latest()
            ->get();

        return response()->json([
            'quotations' => $quotations,
            'total_count' => $quotations->count(),
            'approved_count' => $quotations->where('status.name', 'approved')->count()
        ]);
    }

    public function index()
    {
        $customers = Client::with(['quotations', 'interactions'])
            ->when(!Auth::user()->hasRole('Admin'), function ($query) {
                return $query->where('user_id', Auth::id());
            })
            ->paginate(10);

        return view('customers.index', compact('customers'));
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $interactions = $client->interactions()->latest()->get();
        $serviceHistory = $client->serviceHistory()->latest()->get();
        $quotations = $client->quotations()->latest()->get();

        return view('customers.show', compact('client', 'interactions', 'serviceHistory', 'quotations'));
    }

    public function addInteraction(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'type' => 'required|string',
            'notes' => 'required|string',
            'interaction_date' => 'required|date',
            'status' => 'required|string',
        ]);

        $interaction = new CustomerInteraction($validated);
        $interaction->user_id = Auth::id();
        $client->interactions()->save($interaction);

        return redirect()->back()->with('success', 'Interaction recorded successfully');
    }

    public function addServiceRecord(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'service_type' => 'required|string',
            'description' => 'required|string',
            'service_date' => 'required|date',
            'status' => 'required|string',
            'outcome' => 'nullable|string',
            'quotation_id' => 'nullable|exists:quotations,id',
        ]);

        $serviceRecord = new ServiceHistory($validated);
        $client->serviceHistory()->save($serviceRecord);

        return redirect()->back()->with('success', 'Service record added successfully');
    }
}
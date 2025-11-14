<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Material;
use App\Models\QuotationMaterial;
use App\Models\Quotationstatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;

class QuotationController extends Controller
{
    public function submitComment(Request $request, $token)
    {
        Log::info('Comment submission started', [
            'token' => $token,
            'request_data' => $request->all()
        ]);

        try {
            $quotation = Quotation::where('public_token', $token)->firstOrFail();

            // Validate client access
            $client = $quotation->client;
            $match = (
                strtolower(trim($request->first_name)) === strtolower(trim($client->first_name)) &&
                strtolower(trim($request->last_name)) === strtolower(trim($client->last_name)) &&
                trim($request->phone_number) === trim($client->contact_no)
            );

            if (!$match) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid client information'
                ], 403);
            }
            
            $comment = $quotation->comments()->create([
                'client_id' => $quotation->client_id,
                'comment' => $request->comment
            ]);

            // Update quotation status to feedback when customer comments
            $quotation->update(['feedback_status' => 'feedback']);

            Log::info('Comment created successfully', [
                'comment_id' => $comment->id,
                'comment_data' => $comment->toArray()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comment submitted successfully',
                'comment' => $comment
            ]);
        } catch (\Exception $e) {
            Log::error('Error submitting comment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to submit comment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getComments($quotation)
    {
        try {
            $quotation = Quotation::findOrFail($quotation);
            $comments = $quotation->comments()
                ->with(['client:id,first_name,last_name', 'employee:id,name'])
                ->orderBy('created_at', 'asc')
                ->get();
            
            Log::info('Retrieved comments', [
                'quotation_id' => $quotation->id,
                'comment_count' => $comments->count()
            ]);

            return response()->json($comments);
        } catch (\Exception $e) {
            Log::error('Error getting comments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to get comments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Public comments fetch by public token (no auth required)
     */
    public function getPublicComments($token)
    {
        try {
            $quotation = Quotation::where('public_token', $token)->firstOrFail();
            $comments = $quotation->comments()
                ->with(['client:id,first_name,last_name', 'employee:id,name'])
                ->orderBy('created_at', 'asc')
                ->get();

            Log::info('Retrieved public comments', [
                'public_token' => $token,
                'quotation_id' => $quotation->id,
                'comment_count' => $comments->count()
            ]);

            return response()->json($comments);
        } catch (\Exception $e) {
            Log::error('Error getting public comments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to get comments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function providerReply(Request $request, $quotation)
    {
        try {
            $quotation = Quotation::findOrFail($quotation);
            
            $comment = $quotation->comments()->create([
                'employee_id' => auth()->id(), // Add employee ID
                'comment' => $request->comment
            ]);

            Log::info('Provider reply created', [
                'comment_id' => $comment->id,
                'quotation_id' => $quotation->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reply sent successfully',
                'comment' => $comment
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating provider reply', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to send reply: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approveQuotation($token)
    {
        try {
            $quotation = Quotation::where('public_token', $token)->firstOrFail();
            $quotation->update([
                'customer_approved' => true,
                'feedback_status' => $quotation->provider_approved ? 'approved' : 'pending'
            ]);

            // If both have approved, update the quotation status to Approved
            if ($quotation->isFullyApproved()) {
                $approvedStatus = Quotationstatus::where('status_name', 'Approved')->first();
                if ($approvedStatus) {
                    $quotation->update(['status_id' => $approvedStatus->id]);
                }
            }

            Log::info('Quotation approved by customer', [
                'quotation_id' => $quotation->id,
                'fully_approved' => $quotation->isFullyApproved()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quotation approved successfully',
                'fully_approved' => $quotation->isFullyApproved()
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving quotation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to approve quotation: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Show the public access form for a quotation.
     */
    public function showPublicAccessForm($token)
    {
        $quotation = Quotation::where('public_token', $token)->first();
        if (!$quotation) {
            abort(404);
        }
        return view('public-quotation-access', compact('quotation', 'token'));
    }

    /**
     * Validate client info and grant access to the quotation.
     */
    public function validatePublicAccess($token, Request $request)
    {
        $quotation = Quotation::where('public_token', $token)->first();
        if (!$quotation) {
            abort(404);
        }
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone_number' => 'required|string',
        ]);
        $client = $quotation->client;
        $match = (
            strtolower(trim($request->first_name)) === strtolower(trim($client->first_name)) &&
            strtolower(trim($request->last_name)) === strtolower(trim($client->last_name)) &&
            trim($request->phone_number) === trim($client->contact_no)
        );
        if ($match) {
            // Save session for this token and clear any previous denial
            session(["quotation_public_access_$token" => true]);
            session()->forget("quotation_public_access_denied_$token");
            return redirect()->route('quotation.public.view', ['token' => $token]);
        } else {
            // Deny access, set a flag to block further attempts until correct
            session(["quotation_public_access_denied_$token" => true]);
            return back()->withErrors(['details' => 'Details do not match our records.']);
        }
    }

    /**
     * Show the public quotation if authenticated.
     */
    public function showPublicQuotation($token)
    {
        $quotation = Quotation::where('public_token', $token)->first();
        if (!$quotation) {
            abort(404);
        }
        // Check if permanently denied
        if (session("quotation_public_access_denied_$token")) {
            abort(403, 'Access denied.');
        }
        // Check if authenticated for this token
        if (!session("quotation_public_access_$token")) {
            return redirect()->route('quotation.public.form', ['token' => $token]);
        }
        return view('public-quotation', [
            'quotation' => $quotation,
            'client' => $quotation->client,
            'materials' => $quotation->materials,
            'reports' => $quotation->progressReports ?? collect(),
        ]);
    }
// ...existing code...
    /**
     * Public view for guest/client by token
     */
    public function publicView($token)
    {
        $quotation = Quotation::with(['client', 'employee', 'materials', 'status', 'progressReports', 'revisions'])
            ->where('public_token', $token)
            ->firstOrFail();

        // If quotation is approved, show the report/progress view in a public layout
        if ($quotation->status && strtolower($quotation->status->status_name) === 'approved') {
            return view('view-report', [
                'quotation' => $quotation,
                'client' => $quotation->client,
                'materials' => $quotation->materials,
                'reports' => $quotation->progressReports,
                'revisions' => $quotation->revisions,
                'readonly' => true,
                'layout' => 'layouts.public',
            ]);
        }

        // Otherwise show the read-only quotation view in public layout
        return view('public-quotation', [
            'quotation' => $quotation,
            'client' => $quotation->client,
            'materials' => $quotation->materials,
            'reports' => $quotation->progressReports ?? collect(),
        ]);
    }
    public function viewHome()
    {
        // Get status IDs for Approved and Completed
        $approvedId = \App\Models\QuotationStatus::where('status_name', 'Approved')->value('id');
        $completedId = \App\Models\QuotationStatus::where('status_name', 'Completed')->value('id');

        $totalProjects = \App\Models\Quotation::whereIn('status_id', [$approvedId, $completedId])->count();
        $currentProjects = \App\Models\Quotation::where('status_id', $approvedId)->count();
        $finishedProjects = \App\Models\Quotation::where('status_id', $completedId)->count();

        return view('dashboard', compact('totalProjects', 'currentProjects', 'finishedProjects'));
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
            'public_token' => bin2hex(random_bytes(16)),
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

        $user = auth()->user();
        $quotation = Quotation::findOrFail($id);

        // Helper to check role safely
        function userHasRole($user, $role) {
            return method_exists($user, 'hasRole') ? $user->hasRole($role) : (
                isset($user->roles) && $user->roles->contains('name', $role)
            );
        }

        // Only admin (or manager) can approve (status_id == 2)
        if ($validated['status_id'] == 2) { // Approved
            if (!($user && (userHasRole($user, 'admin') || userHasRole($user, 'manager')))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admin or manager can approve quotations.'
                ], 403);
            }
        }

        // Staff can only set Draft (1) or Rejected (3)
        if ($user && userHasRole($user, 'staff') && $validated['status_id'] == 2) {
            return response()->json([
                'success' => false,
                'message' => 'Staff cannot approve quotations.'
            ], 403);
        }

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
        $quotation = Quotation::with(['client', 'materials'])->findOrFail($id);
        $client = $quotation->client; // Already loaded via with()
        $materials = $quotation->materials; // Already loaded via with()
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
                'id'         => $m->id,
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
        $user = auth()->user();
        // Use the same helper as above
        $userHasRole = function($user, $role) {
            return method_exists($user, 'hasRole') ? $user->hasRole($role) : (
                isset($user->roles) && $user->roles->contains('name', $role)
            );
        };
        if ($user && $userHasRole($user, 'staff')) {
            return response()->json(['error' => 'Staff cannot mark a quotation as completed.'], 403);
        }
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

        // Get latest version number for this quotation
        $latestVersion = $quotation->revisions()->max('version') ?? 0;
        $nextVersion = $latestVersion + 1;

        // Create revision record using the model
        $quotation->revisions()->create([
            'old_data' => $revisionData,  // Model will handle JSON encoding
            'reason'   => $reason,
            'version'  => $nextVersion
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
    public function getCompleted()
    {
        $completed = Quotation::with(['client', 'employee', 'status'])
            ->where('status_id', 4)
            ->get();

        return response()->json($completed);
    }

    /**
     * Allow the service provider to approve a quotation
     */
    public function providerApprove(Request $request, $quotation)
    {
        try {
            $quotation = Quotation::findOrFail($quotation);
            $quotation->update([
                'provider_approved' => true,
                'feedback_status' => $quotation->customer_approved ? 'approved' : 'pending'
            ]);

            // If both have approved, update the quotation status to Approved
            if ($quotation->isFullyApproved()) {
                $approvedStatus = QuotationStatus::where('status_name', 'Approved')->first();
                if ($approvedStatus) {
                    $quotation->update(['status_id' => $approvedStatus->id]);
                }
            }

            Log::info('Quotation approved by provider', [
                'quotation_id' => $quotation->id,
                'employee_id' => auth()->id(),
                'fully_approved' => $quotation->isFullyApproved()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quotation approved by provider successfully',
                'fully_approved' => $quotation->isFullyApproved()
            ]);
        } catch (\Exception $e) {
            Log::error('Error in provider approval', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to approve quotation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRevisionsJson($id)
    {
        $quotation = Quotation::findOrFail($id);

        // Get revisions with automatic JSON decoding through model casting
        $revisions = $quotation->revisions()->orderBy('created_at', 'desc')->get()->map(function ($rev) {
            return [
                'id' => $rev->id,
                'created_at' => $rev->created_at->format('Y-m-d H:i:s'),
                'reason' => $rev->reason,
                'data' => $rev->old_data // already decoded by model casting
            ];
        });

        return response()->json($revisions);
    }

    public function archive()
    {
        $archive = Quotation::with(['client', 'employee', 'status'])
            ->whereHas('status', function ($q) {
                $q->whereIn('status_name', ['Rejected', 'Completed']);
            })
            ->get();

        return response()->json($archive);
    }
}

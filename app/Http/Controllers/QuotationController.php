<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Material;
use App\Models\QuotationMaterial;
use App\Models\QuotationStatus;
use App\Models\AdditionalQuotation;
use App\Helpers\NotificationHelper;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\RejectQuotationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;
use PhpOffice\PhpWord\PhpWord;

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
                $approvedStatus = QuotationStatus::where('status_name', 'Approved')->first();
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
        
        // Load all additional quotations for this parent
        $additionalQuotations = AdditionalQuotation::where('parent_quotation_id', $quotation->id)
            ->with(['materials', 'status'])
            ->get();
        
        return view('public-quotation', [
            'quotation' => $quotation,
            'client' => $quotation->client,
            'materials' => $quotation->materials,
            'reports' => $quotation->progressReports ?? collect(),
            'additionalQuotations' => $additionalQuotations,
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
        // Get status IDs
        $draftId = \App\Models\QuotationStatus::where('status_name', 'Draft')->value('id');
        $approvedId = \App\Models\QuotationStatus::where('status_name', 'Approved')->value('id');
        $rejectedId = \App\Models\QuotationStatus::where('status_name', 'Rejected')->value('id');
        $completedId = \App\Models\QuotationStatus::where('status_name', 'Completed')->value('id');

        // ✅ Enhanced: Count by status
        $draftProjects = \App\Models\Quotation::where('status_id', $draftId)->count();
        $approvedProjects = \App\Models\Quotation::where('status_id', $approvedId)->count();
        $rejectedProjects = \App\Models\Quotation::where('status_id', $rejectedId)->count();
        $ongoingProjects = \App\Models\Quotation::where('status_id', $approvedId)->count();
        
        $totalProjects = \App\Models\Quotation::whereIn('status_id', [$approvedId, $completedId])->count();
        $currentProjects = \App\Models\Quotation::where('status_id', $approvedId)->count();
        $finishedProjects = \App\Models\Quotation::where('status_id', $completedId)->count();

        // ✅ NEW: Get unread notifications count for authenticated user
        $unreadNotifications = 0;
        if (auth()->check()) {
            $unreadNotifications = \App\Models\Notification::where('user_id', auth()->id())
                ->where('read', false)
                ->count();
        }

        // ✅ NEW: Get quotations approved by customers (status = Approved and have customer_approved_at timestamp)
        $customerApprovedQuotations = \App\Models\Quotation::where('status_id', $approvedId)
            ->whereNotNull('approved_by_customer_at')
            ->count();

        return view('dashboard', compact(
            'totalProjects', 
            'currentProjects', 
            'finishedProjects',
            'draftProjects',
            'approvedProjects',
            'rejectedProjects',
            'ongoingProjects',
            'unreadNotifications',
            'customerApprovedQuotations'
        ));
    }

    public function quotationReports()
    {
        // ✅ NEW: Reports page for monthly and yearly statistics
        $draftId = \App\Models\QuotationStatus::where('status_name', 'Draft')->value('id');
        $approvedId = \App\Models\QuotationStatus::where('status_name', 'Approved')->value('id');
        $rejectedId = \App\Models\QuotationStatus::where('status_name', 'Rejected')->value('id');
        $completedId = \App\Models\QuotationStatus::where('status_name', 'Completed')->value('id');

        // Overall statistics
        $stats = [
            'totalProjects' => \App\Models\Quotation::count(),
            'draftProjects' => \App\Models\Quotation::where('status_id', $draftId)->count(),
            'approvedProjects' => \App\Models\Quotation::where('status_id', $approvedId)->count(),
            'rejectedProjects' => \App\Models\Quotation::where('status_id', $rejectedId)->count(),
            'completedProjects' => \App\Models\Quotation::where('status_id', $completedId)->count(),
        ];

        // Monthly breakdown for current year
        $currentYear = \Carbon\Carbon::now()->year;
        $monthlyStats = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $start = \Carbon\Carbon::createFromDate($currentYear, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            
            $monthlyStats[] = [
                'month' => $start->format('M'),
                'fullMonth' => $start->format('F'),
                'monthNum' => $month,
                'draft' => \App\Models\Quotation::where('status_id', $draftId)
                    ->whereBetween('created_at', [$start, $end])->count(),
                'approved' => \App\Models\Quotation::where('status_id', $approvedId)
                    ->whereBetween('created_at', [$start, $end])->count(),
                'rejected' => \App\Models\Quotation::where('status_id', $rejectedId)
                    ->whereBetween('created_at', [$start, $end])->count(),
                'completed' => \App\Models\Quotation::where('status_id', $completedId)
                    ->whereBetween('created_at', [$start, $end])->count(),
            ];
        }

        // Yearly breakdown for last 5 years
        $yearlyStats = [];
        $currentYearNum = \Carbon\Carbon::now()->year;
        
        for ($year = $currentYearNum - 4; $year <= $currentYearNum; $year++) {
            $yearStart = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear();
            $yearEnd = $yearStart->copy()->endOfYear();
            
            $yearlyStats[] = [
                'year' => $year,
                'draft' => \App\Models\Quotation::where('status_id', $draftId)
                    ->whereBetween('created_at', [$yearStart, $yearEnd])->count(),
                'approved' => \App\Models\Quotation::where('status_id', $approvedId)
                    ->whereBetween('created_at', [$yearStart, $yearEnd])->count(),
                'rejected' => \App\Models\Quotation::where('status_id', $rejectedId)
                    ->whereBetween('created_at', [$yearStart, $yearEnd])->count(),
                'completed' => \App\Models\Quotation::where('status_id', $completedId)
                    ->whereBetween('created_at', [$yearStart, $yearEnd])->count(),
            ];
        }

        // Rejection reasons summary
        $rejectionReasons = \App\Models\Quotation::where('status_id', $rejectedId)
            ->whereNotNull('rejection_reason')
            ->selectRaw('rejection_reason, COUNT(*) as count')
            ->groupBy('rejection_reason')
            ->orderByDesc('count')
            ->get()
            ->toArray();

        return view('quotation-reports', compact('stats', 'monthlyStats', 'yearlyStats', 'rejectionReasons'));
    }
    public function viewReport($id)
    {
        $quotation = Quotation::with(['client', 'employee', 'materials', 'comments.replies', 'progressReports'])->findOrFail($id);
        $reports = $quotation->progressReports()->orderByDesc('created_at')->get();
        return view('view-report', compact('quotation', 'reports'));
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
            'employee_id'  => auth()->id(), // Use authenticated user
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
            'contract_subject' => 'nullable|string|max:255',
            'project_start_date' => 'nullable|date',
            'project_end_date' => 'nullable|date',
            'with_contract' => 'nullable|boolean',
            'rejection_reason' => 'nullable|string|max:1000', // ✅ NEW: Add rejection reason
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

            // ✅ ENFORCE: With Contract checkbox MUST be true for approval
            if (!$validated['with_contract']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contract must be confirmed to approve this quotation.'
                ], 422);
            }

            // ✅ ENFORCE: Contract subject is REQUIRED for approval
            if (empty($validated['contract_subject'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contract subject is required to approve.'
                ], 422);
            }

            // ✅ ENFORCE: Start date is REQUIRED for approval
            if (empty($validated['project_start_date'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project start date is required to approve.'
                ], 422);
            }

            // ✅ ENFORCE: End date is REQUIRED for approval
            if (empty($validated['project_end_date'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project end date is required to approve.'
                ], 422);
            }

            // ✅ ENFORCE: Start date must be before end date
            $startDate = \Carbon\Carbon::parse($validated['project_start_date']);
            $endDate = \Carbon\Carbon::parse($validated['project_end_date']);
            if ($startDate->greaterThanOrEqualTo($endDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project start date must be before end date.'
                ], 422);
            }
        }

        // ✅ NEW: Enforce rejection reason is REQUIRED for rejection
        if ($validated['status_id'] == 3) { // Rejected
            if (empty($validated['rejection_reason'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rejection reason is required.'
                ], 422);
            }
        }

        // Staff can only set Draft (1) or Rejected (3)
        if ($user && userHasRole($user, 'staff') && $validated['status_id'] == 2) {
            return response()->json([
                'success' => false,
                'message' => 'Staff cannot approve quotations.'
            ], 403);
        }

        // Update quotation with status and optional contract fields
        $quotation->status_id = $validated['status_id'];
        
        // If contract data is provided (approve action), store it
        if ($validated['status_id'] == 2) {
            $quotation->contract_subject = $validated['contract_subject'] ?? null;
            $quotation->project_start_date = $validated['project_start_date'] ?? null;
            $quotation->project_end_date = $validated['project_end_date'] ?? null;
            $quotation->with_contract = $validated['with_contract'] ?? false;
        }

        // ✅ NEW: If rejecting, store the rejection reason
        if ($validated['status_id'] == 3) {
            $quotation->rejection_reason = $validated['rejection_reason'] ?? null;
        }
        
        $quotation->save();

        // ✅ NEW: Create notifications for status changes
        if ($validated['status_id'] == 2) { // Approved
            NotificationHelper::notifyQuotationApproved($quotation);
        } elseif ($validated['status_id'] == 3) { // Rejected
            NotificationHelper::notifyQuotationRejected($quotation);
        }

        $statusMessages = [
            1 => 'Quotation saved as draft!',
            2 => 'Quotation approved successfully!',
            3 => 'Quotation rejected successfully!'
        ];

        return response()->json([
            'success' => true,
            'message' => $statusMessages[$validated['status_id']] ?? 'Status updated successfully!',
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

    /**
     * Generate public access token for quotation
     */
    public function generateToken($id)
    {
        try {
            $quotation = Quotation::findOrFail($id);

            // Check authorization - allow creator or admin
            $user = auth()->user();
            $isCreator = auth()->id() == $quotation->employee_id;
            $isAdmin = false;
            
            try {
                $isAdmin = $user->hasRole('admin');
            } catch (\Exception $e) {
                $isAdmin = false;
            }
            
            if (!$isCreator && !$isAdmin) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Generate new token
            $token = bin2hex(random_bytes(32));
            $quotation->update(['public_token' => $token]);

            $publicLink = route('quotation.public.view', ['token' => $token]);

            Log::info('Quotation public token generated', [
                'quotation_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Public link generated successfully',
                'token' => $token,
                'public_link' => $publicLink,
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating quotation token', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate token',
            ], 500);
        }
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
                // format timestamps explicitly in the application timezone
                'created_at' => $rev->created_at->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                'reason' => $rev->reason,
                'data' => $rev->old_data // already decoded by model casting
            ];
        });

        return response()->json($revisions);
    }

    /**
     * Get all additional quotations linked to a parent quotation as JSON
     * Uses the new additional_quotations table (Option 2 design)
     * 
     * @param int $id The parent quotation ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdditionalQuotationsJson($id)
    {
        try {
            $parentQuotation = Quotation::findOrFail($id);

            // Get all additional quotations (nested components) with their materials
            $additionalQuotations = $parentQuotation->additionalQuotations()
                ->with('materials', 'status')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($quotation) {
                    return [
                        'id' => $quotation->id,
                        'parent_quotation_id' => $quotation->parent_quotation_id,
                        'subject' => $quotation->subject,
                        'description' => $quotation->description,
                        'progress' => $quotation->progress,
                        'status_name' => $quotation->status->status_name ?? 'Unknown',
                        // ensure timestamps are returned in the configured application timezone
                        'created_at' => $quotation->created_at->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                        'created_date' => $quotation->created_at->setTimezone(config('app.timezone'))->format('M d, Y'),
                        'materials_count' => $quotation->materials->count(),
                        'material_total' => number_format($quotation->getMaterialTotal(), 2),
                    ];
                });

            Log::info('Additional quotations fetched', [
                'parent_quotation_id' => $id,
                'count' => $additionalQuotations->count(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'quotations' => $additionalQuotations,
                'total' => $additionalQuotations->count(),
                'parent_quotation_id' => $id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching additional quotations', [
                'parent_quotation_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load additional quotations'
            ], 500);
        }
    }

    /**
     * Reject a quotation with a required reason
     */
    public function reject($quotation, RejectQuotationRequest $request)
    {
        try {
            $quotation = Quotation::findOrFail($quotation);

            // Check authorization - only the creator can reject their quotation
            if (auth()->id() !== $quotation->employee_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'You are not authorized to reject this quotation'
                ], 403);
            }

            // Prevent re-rejection
            if ($quotation->isRejected()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This quotation has already been rejected'
                ], 422);
            }

            // Reject the quotation
            $quotation->reject($request->rejection_reason, auth()->id());

            Log::info('Quotation rejected', [
                'quotation_id' => $quotation->id,
                'rejected_by' => auth()->id(),
                'reason' => substr($request->rejection_reason, 0, 100)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quotation rejected successfully',
                'quotation' => $quotation->load('rejectedBy')
            ]);
        } catch (\Exception $e) {
            Log::error('Error rejecting quotation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to reject quotation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a linked quotation (add-on)
     */
    public function createLinkedQuotation(StoreQuotationRequest $request, $parentQuotationId)
    {
        try {
            $parentQuotation = Quotation::findOrFail($parentQuotationId);

            // Check authorization - only the creator can manage linked quotations
            if (auth()->id() !== $parentQuotation->employee_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'You are not authorized to manage this quotation'
                ], 403);
            }

            // Create linked quotation
            $linkedQuotation = Quotation::create([
                'subject' => $request->subject,
                'description' => $request->description,
                'employee_id' => auth()->id(),
                'client_id' => $parentQuotation->client_id,
                'status_id' => $request->status_id,
                'labor_fee' => $request->labor_fee ?? 0,
                'delivery_fee' => $request->delivery_fee ?? 0,
                'parent_quotation_id' => $parentQuotationId,
                'quotation_type' => 'addon',
                'public_token' => bin2hex(random_bytes(16)),
            ]);

            Log::info('Linked quotation created', [
                'parent_quotation_id' => $parentQuotationId,
                'linked_quotation_id' => $linkedQuotation->id,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Add-on quotation created successfully',
                'quotation' => $linkedQuotation->load(['client', 'employee', 'status'])
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating linked quotation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to create add-on quotation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all quotations linked to a specific quotation
     */
    public function getLinkedQuotations($quotation)
    {
        try {
            $quotation = Quotation::findOrFail($quotation);
            $linkedQuotations = $quotation->getAllLinkedQuotations();

            return response()->json([
                'success' => true,
                'quotations' => $linkedQuotations->load(['client', 'employee', 'status', 'rejectedBy'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting linked quotations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to get linked quotations: ' . $e->getMessage()
            ], 500);
        }
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

    /**
     * Store a newly created additional quotation linked to a parent quotation.
     * 
     * Creates a child quotation with the same client as the parent but allows
     * separate materials, fees, and subject. Useful for supplementary work orders.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Store a new additional quotation (nested component)
     * Uses the new additional_quotations table (Option 2 design)
     */
    public function storeAdditionalQuotation(Request $request)
    {
        $validated = $request->validate([
            'parent_quotation_id' => 'required|integer|exists:quotations,id',
            'subject'             => 'required|string|max:255',
            'description'         => 'nullable|string|max:1000',
        ]);

        try {
            // Verify parent quotation exists and user has access
            $parentQuotation = Quotation::findOrFail($validated['parent_quotation_id']);

            // Authorization check - only owner or staff can create additional quotations
            $user = auth()->user();
            $isAuthorized = auth()->id() === $parentQuotation->employee_id 
                || (isset($user->roles) && ($user->roles->contains('name', 'staff') || $user->roles->contains('name', 'admin')));
            
            if (!$isAuthorized) {
                Log::warning('Unauthorized attempt to create additional quotation', [
                    'parent_quotation_id' => $validated['parent_quotation_id'],
                    'user_id' => auth()->id(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to create additional quotations for this quotation.',
                ], 403);
            }

            // Create metadata-only additional quotation (not independent)
            // This will be edited in a separate template with materials/fees
            $additionalQuotation = AdditionalQuotation::create([
                'parent_quotation_id' => $validated['parent_quotation_id'],
                'subject'             => $validated['subject'],
                'description'         => $validated['description'] ?? '',
                'progress'            => 0,  // 0-100, starts at draft
            ]);

            Log::info('Additional quotation created successfully', [
                'additional_quotation_id' => $additionalQuotation->id,
                'parent_quotation_id' => $validated['parent_quotation_id'],
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'additional_quotation_id' => $additionalQuotation->id,  // Return the additional quotation ID
                'message' => 'Additional quotation created successfully! Redirecting to editor...',
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Parent quotation not found when creating additional quotation', [
                'parent_quotation_id' => $validated['parent_quotation_id'] ?? null,
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Parent quotation not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error creating additional quotation', [
                'parent_quotation_id' => $validated['parent_quotation_id'] ?? null,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create additional quotation. Please try again later.',
            ], 500);
        }
    }

    /**
     * Show the form for editing an additional quotation
     * Now uses the same unified quotation.blade.php as regular quotations
     */
    public function editAdditionalQuotation($id)
    {
        try {
            $additionalQuotation = AdditionalQuotation::with([
                'materials',
                'parentQuotation.client',
                'status',
                'comments.replies.nestedReplies'  // ✅ Load comments for additional quotation with replies
            ])->findOrFail($id);

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                abort(403, 'Unauthorized');
            }

            // If approved (customer_approved OR status_id >= 2), redirect to view route instead
            if ($additionalQuotation->customer_approved || $additionalQuotation->status_id >= 2) {
                return redirect()->route('additional-quotations.view', ['id' => $id]);
            }

            // Extract materials for blade view
            $materials = $additionalQuotation->materials;
            $availableMaterials = Material::all();
            $readonly = false; // Allow editing for draft/pending states

            // Return unified quotation view with additionalQuotation (view detects this)
            return view('quotation', compact('additionalQuotation', 'materials', 'availableMaterials', 'readonly'));
        } catch (\Exception $e) {
            Log::error('Error loading additional quotation', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            abort(404, 'Additional quotation not found');
        }
    }

    /**
     * View an approved additional quotation (read-only, using view-report template)
     */
    public function viewAdditionalQuotation($id)
    {
        try {
            $additionalQuotation = AdditionalQuotation::with('materials', 'parentQuotation.client', 'status')
                ->findOrFail($id);

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                abort(403, 'Unauthorized');
            }

            // Prepare data for view-report template
            // Rename additionalQuotation to quotation so view-report.blade.php uses it
            $quotation = $additionalQuotation;
            $isAdditional = true;
            $readonly = true; // Read-only view
            $layout = 'layouts.app';

            // Get progress reports and comments from the parent quotation
            $reports = $additionalQuotation->parentQuotation->progressReports()->orderByDesc('created_at')->get();
            
            // Get comments from the parent quotation (they're shared) with replies
            $parentComments = $additionalQuotation->parentQuotation->comments()->with('replies')->orderByDesc('created_at')->get();

            // Return view-report template with quotation data
            return view('view-report', compact('quotation', 'isAdditional', 'readonly', 'layout', 'reports', 'parentComments'));
        } catch (\Exception $e) {
            Log::error('Error viewing additional quotation', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            abort(404, 'Additional quotation not found');
        }
    }

    /**
     * Update an additional quotation with fees
     */
    public function updateAdditionalQuotation(Request $request, $id)
    {
        try {
            $additionalQuotation = AdditionalQuotation::findOrFail($id);

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'labor_fee' => 'nullable|numeric|min:0',
                'delivery_fee' => 'nullable|numeric|min:0',
            ]);

            $additionalQuotation->update([
                'labor_fee' => $validated['labor_fee'] ?? 0,
                'delivery_fee' => $validated['delivery_fee'] ?? 0,
            ]);

            Log::info('Additional quotation updated', [
                'additional_quotation_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fees updated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating additional quotation', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update additional quotation',
            ], 500);
        }
    }

    /**
     * Attach a material to an additional quotation
     */
    public function attachMaterialToAdditional(Request $request, $id)
    {
        try {
            $additionalQuotation = AdditionalQuotation::findOrFail($id);

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'material_id' => 'required|exists:materials,id',
                'quantity' => 'required|numeric|min:0.01',
            ]);

            // Check if material already attached
            if ($additionalQuotation->materials()->where('material_id', $validated['material_id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Material already attached to this quotation',
                ], 400);
            }

            $additionalQuotation->materials()->attach(
                $validated['material_id'],
                ['quantity' => $validated['quantity']]
            );

            Log::info('Material attached to additional quotation', [
                'additional_quotation_id' => $id,
                'material_id' => $validated['material_id'],
                'quantity' => $validated['quantity'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Material added successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error attaching material', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to attach material',
            ], 500);
        }
    }

    /**
     * Detach a material from an additional quotation
     */
    public function detachMaterialFromAdditional($id, $materialId)
    {
        try {
            $additionalQuotation = AdditionalQuotation::findOrFail($id);

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $additionalQuotation->materials()->detach($materialId);

            Log::info('Material detached from additional quotation', [
                'additional_quotation_id' => $id,
                'material_id' => $materialId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Material removed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error detaching material', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove material',
            ], 500);
        }
    }

    /**
     * Approve and attach additional quotation to parent
     */
    public function approveAdditionalQuotation($id)
    {
        try {
            $additionalQuotation = AdditionalQuotation::findOrFail($id);

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Check if customer has approved the additional quotation
            if (!$additionalQuotation->customer_approved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer has not approved this additional quotation yet. Please wait for customer approval before attaching.',
                ], 422);
            }

            // Set progress to 100 (approved)
            $additionalQuotation->update(['progress' => 100]);

            Log::info('Additional quotation approved', [
                'additional_quotation_id' => $id,
                'parent_quotation_id' => $additionalQuotation->parent_quotation_id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Additional quotation approved and attached to parent',
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving additional quotation', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve additional quotation',
            ], 500);
        }
    }

    /**
     * Delete an additional quotation
     */
    public function deleteAdditionalQuotation($id)
    {
        try {
            $additionalQuotation = AdditionalQuotation::findOrFail($id);

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $additionalQuotation->delete();

            Log::info('Additional quotation deleted', [
                'additional_quotation_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Additional quotation deleted',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting additional quotation', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete additional quotation',
            ], 500);
        }
    }

    /**
     * Get materials for an additional quotation
     */
    public function getAdditionalMaterials($id)
    {
        try {
            $additionalQuotation = AdditionalQuotation::with(['materials'])->findOrFail($id);

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $materials = $additionalQuotation->materials->map(function ($material) {
                return [
                    'id' => $material->id,
                    'pivot_id' => $material->pivot->id,
                    'name' => $material->name,
                    'unit' => $material->unit,
                    'unit_price' => (float) ($material->pivot->unit_cost ?? 0),
                    'quantity' => (int) $material->pivot->quantity,
                    'line_total' => (float) ($material->pivot->unit_cost * $material->pivot->quantity),
                ];
            });

            return response()->json([
                'success' => true,
                'materials' => $materials,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting additional quotation materials', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get materials',
            ], 500);
        }
    }

    /**
     * Store selected materials to additional quotation
     */
    public function storeSelectedMaterialsToAdditional(Request $request)
    {
        $data = $request->validate([
            'quot_id'   => 'required|integer|exists:additional_quotations,id',
            'selected'  => 'required|array',
            'quantity'  => 'array', // quantities keyed by material id
        ]);

        $additionalQuotation = \App\Models\AdditionalQuotation::with(['materials'])->findOrFail($data['quot_id']);   
        $selected = $data['selected'];
        $quantities = $request->input('quantity', []);

        foreach ($selected as $matId) {
            $qty = isset($quantities[$matId]) ? (int) $quantities[$matId] : 1;
            if ($qty < 1) $qty = 1;

            $existing = $additionalQuotation->materials()->wherePivot('material_id', $matId)->first();     
            if ($existing) {
                $newQty = ($existing->pivot->quantity ?? 0) + $qty;
                $additionalQuotation->materials()->updateExistingPivot($matId, [
                    'quantity'  => $newQty,
                    'unit_cost' => $existing->unit_price,
                ]);
            } else {
                $material = \App\Models\Material::find($matId);
                $additionalQuotation->materials()->attach($matId, [
                    'quantity'  => $qty,
                    'unit_cost' => $material ? $material->unit_price : 0,
                ]);
            }
        }

        $additionalQuotation->load(['materials']);

        $materials = $additionalQuotation->materials->map(function ($m) {
            return [
                'id'         => $m->id,
                'pivot_id'   => $m->pivot->id,
                'name'       => $m->name,
                'unit'       => $m->unit,
                'unit_price' => (float) $m->pivot->unit_cost,
                'quantity'   => (int) $m->pivot->quantity,
                'line_total' => (float) ($m->pivot->unit_cost * $m->pivot->quantity),
            ];
        })->values();

        $materialsSubtotal = $materials->sum('line_total');
        $labor = (float) ($additionalQuotation->labor_fee ?? 0);
        $delivery = (float) ($additionalQuotation->delivery_fee ?? 0);
        $grandTotal = $materialsSubtotal + $labor + $delivery;

        return response()->json([
            'success'      => true,
            'message'      => 'Materials added/updated on additional quotation',
            'materials'    => $materials,
            'subtotal'     => $materialsSubtotal,
            'labor_fee'    => $labor,
            'delivery_fee' => $delivery,
            'grand_total'  => $grandTotal,
        ]);
    }

    /**
     * Update price of a material in additional quotation
     */
    public function updateAdditionalMaterialPrice(Request $request, $pivotId)
    {
        try {
            $validated = $request->validate([
                'unit_cost' => 'required|numeric|min:0',
            ]);

            // Find the pivot record
            $pivot = \App\Models\AdditionalQuotationMaterial::findOrFail($pivotId);
            $additionalQuotation = $pivot->additionalQuotation;

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Update the pivot unit cost
            $pivot->unit_cost = $validated['unit_cost'];
            $pivot->save();

            // Reload to get updated pivot
            $additionalQuotation->load('materials');

            // Calculate totals
            $lineTotal = $validated['unit_cost'] * $pivot->quantity;
            $materialsSubtotal = $additionalQuotation->materials->sum(fn($m) => $m->pivot->unit_cost * $m->pivot->quantity);
            $grandTotal = $materialsSubtotal + $additionalQuotation->labor_fee + $additionalQuotation->delivery_fee;

            Log::info('Additional quotation material price updated', [
                'pivot_id' => $pivotId,
                'unit_cost' => $validated['unit_cost'],
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Price updated successfully',
                'line_total' => $lineTotal,
                'materials_total' => $materialsSubtotal,
                'grand_total' => $grandTotal,
                'labor_fee' => $additionalQuotation->labor_fee,
                'delivery_fee' => $additionalQuotation->delivery_fee,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating additional material price', [
                'pivot_id' => $pivotId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update price',
            ], 500);
        }
    }

    /**
     * Update quantity of a material in additional quotation
     */
    public function updateAdditionalMaterialQuantity(Request $request, $pivotId)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|numeric|min:0.01',
            ]);

            // Find the pivot record
            $pivot = \App\Models\AdditionalQuotationMaterial::findOrFail($pivotId);
            $additionalQuotation = $pivot->additionalQuotation;

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $pivot->quantity = $validated['quantity'];
            $pivot->save();

            // Reload to get updated pivot
            $additionalQuotation->load('materials');

            // Calculate totals
            $lineTotal = $pivot->unit_cost * $validated['quantity'];
            $materialsSubtotal = $additionalQuotation->materials->sum(fn($m) => $m->pivot->unit_cost * $m->pivot->quantity);
            $grandTotal = $materialsSubtotal + $additionalQuotation->labor_fee + $additionalQuotation->delivery_fee;

            Log::info('Additional quotation material quantity updated', [
                'pivot_id' => $pivotId,
                'quantity' => $validated['quantity'],
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quantity updated successfully',
                'line_total' => $lineTotal,
                'materials_total' => $materialsSubtotal,
                'grand_total' => $grandTotal,
                'labor_fee' => $additionalQuotation->labor_fee,
                'delivery_fee' => $additionalQuotation->delivery_fee,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating additional material quantity', [
                'pivot_id' => $pivotId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update quantity',
            ], 500);
        }
    }

    /**
     * Delete a material from additional quotation
     */
    public function deleteAdditionalQuotationMaterial($pivotId)
    {
        try {
            $pivot = \App\Models\AdditionalQuotationMaterial::findOrFail($pivotId);
            $additionalQuotation = $pivot->additionalQuotation;

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $pivot->delete();

            // Reload materials to get updated totals
            $additionalQuotation->load('materials');

            // Calculate new totals
            $materialsSubtotal = $additionalQuotation->materials->sum(fn($m) => $m->pivot->unit_cost * $m->pivot->quantity);
            $grandTotal = $materialsSubtotal + $additionalQuotation->labor_fee + $additionalQuotation->delivery_fee;

            Log::info('Additional quotation material deleted', [
                'pivot_id' => $pivotId,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Material removed successfully',
                'materials_total' => $materialsSubtotal,
                'grand_total' => $grandTotal,
                'labor_fee' => $additionalQuotation->labor_fee,
                'delivery_fee' => $additionalQuotation->delivery_fee,
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting additional quotation material', [
                'pivot_id' => $pivotId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete material',
            ], 500);
        }
    }

        /**
     * Update status of additional quotation (Draft/Reject)
     */
    public function updateAdditionalQuotationStatus(Request $request, $id)
    {
        try {
            $additionalQuotation = AdditionalQuotation::findOrFail($id);

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'status_id' => 'required|integer|in:1,2,3',  // 1=Draft, 2=Approved, 3=Rejected
                'rejection_reason' => 'required_if:status_id,3|nullable|string|max:1000',
            ]);

            // If approving (status 2), handle attachment to parent quotation
            if ($validated['status_id'] == 2) {
                // Verify customer approved it first
                if (!$additionalQuotation->customer_approved) {
                    return response()->json(['success' => false, 'message' => 'Customer has not approved this quotation yet'], 400);
                }

                // Update status and progress
                $additionalQuotation->update([
                    'status_id' => 2,
                    'progress' => 100,
                ]);

                // Get parent quotation and attach materials
                $parentQuotation = $additionalQuotation->parentQuotation;

                foreach ($additionalQuotation->materials as $material) {
                    $existing = $parentQuotation->materials()
                        ->where('material_id', $material->id)
                        ->first();

                    if ($existing) {
                        // Update quantity and cost if already exists
                        $parentQuotation->materials()->updateExistingPivot($material->id, [
                            'quantity' => ($existing->pivot->quantity ?? 0) + $material->pivot->quantity,
                            'unit_cost' => $material->pivot->unit_cost,
                        ]);
                    } else {
                        // Attach new material
                        $parentQuotation->materials()->attach($material->id, [
                            'quantity' => $material->pivot->quantity,
                            'unit_cost' => $material->pivot->unit_cost,
                        ]);
                    }
                }

                Log::info('Additional quotation approved and materials attached', [
                    'additional_quotation_id' => $id,
                    'parent_quotation_id' => $parentQuotation->id,
                    'user_id' => auth()->id(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Additional quotation approved and materials attached to parent',
                    'quotation' => [
                        'id' => $additionalQuotation->id,
                        'status' => ['status_name' => 'Approved'],
                        'customer_approved' => true,
                    ],
                ]);
            }

            // For Draft or Reject actions
            $additionalQuotation->update([
                'status_id' => $validated['status_id'],
                'rejection_reason' => $validated['rejection_reason'] ?? null,
            ]);

            Log::info('Additional quotation status updated', [
                'additional_quotation_id' => $id,
                'status_id' => $validated['status_id'],
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => $validated['status_id'] == 1 ? 'Saved as draft' : 'Rejected successfully',
                'quotation' => [
                    'id' => $additionalQuotation->id,
                    'status' => ['status_name' => $validated['status_id'] == 1 ? 'Draft' : 'Rejected'],
                    'customer_approved' => $additionalQuotation->customer_approved,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error updating additional quotation status - validation', [
                'id' => $id,
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . json_encode($e->errors()),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating additional quotation status', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
            ], 500);
        }
    }

    /**
     * Update fees for additional quotation
     */
    public function updateAdditionalQuotationFee(Request $request, $id)
    {
        try {
            $additionalQuotation = AdditionalQuotation::findOrFail($id);

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'field' => 'required|in:labor_fee,delivery_fee',
                'value' => 'required|numeric|min:0',
            ]);

            // Cast value to float for safety
            $value = floatval($validated['value']);
            
            $additionalQuotation->update([
                $validated['field'] => $value,
            ]);

            Log::info('Additional quotation fee updated', [
                'additional_quotation_id' => $id,
                'field' => $validated['field'],
                'value' => $value,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fee updated successfully',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . json_encode($e->errors()),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating additional quotation fee', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update fee: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a revision of an additional quotation
     */
    public function createAdditionalRevision(Request $request, $id)
    {
        try {
            $additionalQuotation = AdditionalQuotation::with(['materials'])->findOrFail($id);

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'reason' => 'required|string|max:1000',
            ]);

            // Store the old data as JSON (matching quotation revision structure)
            $revisionData = [
                'subject'      => $additionalQuotation->subject,
                'description'  => $additionalQuotation->description,
                'labor_fee'    => $additionalQuotation->labor_fee,
                'delivery_fee' => $additionalQuotation->delivery_fee,
                'status_id'    => $additionalQuotation->status_id,
                'parent_quotation_id' => $additionalQuotation->parent_quotation_id,
                'materials'    => $additionalQuotation->materials->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'name' => $m->name,
                        'unit' => $m->unit,
                        'unit_cost' => $m->pivot->unit_cost,
                        'quantity' => $m->pivot->quantity,
                    ];
                })->toArray(),
            ];

            // Create revision record
            $revision = new \App\Models\QuotationRevision();
            $revision->quotation_type = 'additional';
            $revision->quotation_id = $id;
            $revision->created_by = auth()->id();
            $revision->change_reason = $validated['reason'];
            $revision->old_data = $revisionData;
            $revision->save();

            // Reset progress to 0 for new draft
            $additionalQuotation->update(['progress' => 0]);

            Log::info('Additional quotation revision created', [
                'additional_quotation_id' => $id,
                'revision_id' => $revision->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Revision created successfully',
                'revision_id' => $revision->id,
                'new_revision_id' => $id, // Return the current additional quotation ID as the new revision
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating additional quotation revision', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create revision: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get revision history for additional quotation as JSON
     */
    public function getAdditionalRevisionsJson($id)
    {
        try {
            $additionalQuotation = AdditionalQuotation::findOrFail($id);

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $revisions = \App\Models\QuotationRevision::where('quotation_type', 'additional')
                ->where('quotation_id', $id)
                ->with('createdBy')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($revision) {
                    return [
                            'id' => $revision->id,
                            'created_at' => $revision->created_at->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                            'reason' => $revision->change_reason,
                            'created_by' => $revision->createdBy?->name ?? 'Unknown',
                            'data' => $revision->old_data // already decoded by model casting
                        ];
                });

            return response()->json($revisions);
        } catch (\Exception $e) {
            Log::error('Error getting additional quotation revisions', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([], 500);
        }
    }

    /**
     * Get additional quotation revisions for public access (token-based)
     */
    public function getAdditionalPublicRevisionsJson($token)
    {
        try {
            $additionalQuotation = AdditionalQuotation::where('public_token', $token)->firstOrFail();

            $revisions = \App\Models\QuotationRevision::where('quotation_type', 'additional')
                ->where('quotation_id', $additionalQuotation->id)
                ->with('createdBy')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($revision) {
                    return [
                        'id' => $revision->id,
                        'created_at' => $revision->created_at->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                        'reason' => $revision->change_reason,
                        'created_by' => $revision->createdBy?->name ?? 'Unknown',
                        'data' => $revision->old_data // already decoded by model casting
                    ];
                });

            return response()->json($revisions);
        } catch (\Exception $e) {
            Log::error('Error getting additional quotation public revisions', [
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage(),
            ]);

            return response()->json([], 404);
        }
    }

    /**
     * Generate a public token for additional quotation
     */
    public function generateAdditionalToken($id)
    {
        try {
            $additionalQuotation = AdditionalQuotation::findOrFail($id);

            // Check authorization - allow creator or admin
            $user = auth()->user();
            $isCreator = auth()->id() == $additionalQuotation->parentQuotation->employee_id;
            $isAdmin = false;
            
            try {
                $isAdmin = $user->hasRole('admin');
            } catch (\Exception $e) {
                $isAdmin = false;
            }
            
            if (!$isCreator && !$isAdmin) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Use parent quotation's token for unified public link
            $parentQuotation = $additionalQuotation->parentQuotation;
            
            // If parent doesn't have a token yet, generate one
            if (!$parentQuotation->public_token) {
                $token = bin2hex(random_bytes(32));
                $parentQuotation->update(['public_token' => $token]);
            } else {
                $token = $parentQuotation->public_token;
            }

            // Additional quotation uses parent's token (no separate token)
            $publicLink = route('quotation.public.view', ['token' => $token]);

            Log::info('Additional quotation public token generated (using parent token)', [
                'additional_quotation_id' => $id,
                'parent_quotation_id' => $parentQuotation->id,
                'token' => $token,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Public link generated successfully',
                'token' => $token,
                'public_link' => $publicLink,
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating additional quotation token', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate token',
            ], 500);
        }
    }

    /**
     * Approve additional quotation and attach its materials to parent quotation
     */
    public function approveAndAttachAdditionalQuotation($id)
    {
        try {
            $additionalQuotation = AdditionalQuotation::with(['materials', 'parentQuotation'])->findOrFail($id);

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Verify customer approved it first
            if (!$additionalQuotation->customer_approved) {
                return response()->json(['success' => false, 'message' => 'Customer has not approved this quotation yet'], 400);
            }

            // Update additional quotation status to approved (progress = 100)
            $additionalQuotation->update([
                'progress' => 100,
                'status_id' => 2, // Approved status
            ]);

            // Get parent quotation
            $parentQuotation = $additionalQuotation->parentQuotation;

            // Attach all materials from additional quotation to parent quotation
            foreach ($additionalQuotation->materials as $material) {
                $existing = $parentQuotation->materials()
                    ->where('material_id', $material->id)
                    ->first();

                if ($existing) {
                    // Update quantity and cost if already exists
                    $parentQuotation->materials()->updateExistingPivot($material->id, [
                        'quantity' => ($existing->pivot->quantity ?? 0) + $material->pivot->quantity,
                        'unit_cost' => $material->pivot->unit_cost,
                    ]);
                } else {
                    // Attach new material
                    $parentQuotation->materials()->attach($material->id, [
                        'quantity' => $material->pivot->quantity,
                        'unit_cost' => $material->pivot->unit_cost,
                    ]);
                }
            }

            Log::info('Additional quotation approved and attached to parent', [
                'additional_quotation_id' => $id,
                'parent_quotation_id' => $parentQuotation->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Additional quotation approved and materials attached to parent quotation',
                'redirect' => route('quotations.edit', ['id' => $parentQuotation->id]),
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving and attaching additional quotation', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve and attach quotation',
            ], 500);
        }
    }

    /**
     * Export additional quotation to DOC
     */
    public function exportAdditionalQuotation($id)
    {
        try {
            $additionalQuotation = AdditionalQuotation::with('materials', 'parentQuotation.client')->findOrFail($id);

            // Check authorization
            if (auth()->id() != $additionalQuotation->parentQuotation->employee_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Use PhpOffice\PhpWord to generate DOCX
            $phpWord = new PhpWord();
            $section = $phpWord->addSection();

            // HEADER: single image at top
            $headerPath = public_path('Image/header.png');
            if (file_exists($headerPath)) {
                $section->addImage($headerPath, [
                    'width' => 400,
                    'height' => 100,
                    'alignment' => 'center'
                ]);
            }
            $section->addTextBreak(2);

            // QUOTATION TITLE
            $section->addText('ADDITIONAL QUOTATION DETAILS', ['bold' => true, 'size' => 16], ['alignment' => 'center']);
            $section->addTextBreak(1);

            // Parent Quotation Info
            $section->addText('Parent Quotation: ' . $additionalQuotation->parentQuotation->subject, ['bold' => true, 'size' => 12]);
            $section->addTextBreak(1);

            // Quotation details
            $section->addText("Subject: {$additionalQuotation->subject}");
            // Use application timezone when rendering exported dates
            $section->addText("Date: " . $additionalQuotation->created_at->setTimezone(config('app.timezone'))->format('F d, Y'));
            if ($additionalQuotation->description) {
                $section->addText("Description: {$additionalQuotation->description}");
            }
            $section->addTextBreak(1);

            // Client Info
            $section->addText('Client Information', ['bold' => true, 'size' => 14]);
            $section->addText("Name: {$additionalQuotation->parentQuotation->client->first_name} {$additionalQuotation->parentQuotation->client->last_name}");
            $section->addText("Contact: {$additionalQuotation->parentQuotation->client->contact_no}");
            $section->addText("Address: {$additionalQuotation->parentQuotation->client->address}");
            $section->addTextBreak(1);

            // Materials
            $section->addText('Materials', ['bold' => true, 'size' => 12]);
            $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
            $table->addRow();
            $table->addCell(4000)->addText('Material', ['bold' => true]);
            $table->addCell(2000)->addText('Quantity', ['bold' => true]);
            $table->addCell(2000)->addText('Unit Price', ['bold' => true]);
            $table->addCell(2000)->addText('Total', ['bold' => true]);

            $materialTotal = 0;
            foreach ($additionalQuotation->materials as $material) {
                $lineTotal = $material->pivot->unit_cost * $material->pivot->quantity;
                $materialTotal += $lineTotal;

                $table->addRow();
                $table->addCell(4000)->addText($material->name);
                $table->addCell(2000)->addText($material->pivot->quantity . ' ' . $material->unit);
                $table->addCell(2000)->addText('₱' . number_format($material->pivot->unit_cost, 2));
                $table->addCell(2000)->addText('₱' . number_format($lineTotal, 2));
            }

            $section->addTextBreak(1);

            // Fees and Grand Total
            $section->addText('Cost Summary', ['bold' => true, 'size' => 12]);
            $section->addText("Materials Total: ₱" . number_format($materialTotal, 2));
            $section->addText("Labor Fee: ₱" . number_format($additionalQuotation->labor_fee ?? 0, 2));
            $section->addText("Delivery/Hauling Fee: ₱" . number_format($additionalQuotation->delivery_fee ?? 0, 2));
            
            $grandTotal = $materialTotal + ($additionalQuotation->labor_fee ?? 0) + ($additionalQuotation->delivery_fee ?? 0);
            $section->addText("GRAND TOTAL: ₱" . number_format($grandTotal, 2), ['bold' => true, 'size' => 14]);

            // Save and download
            $fileName = 'Additional_Quotation_' . $additionalQuotation->id . '_' . date('Ymd_His') . '.docx';
            $filePath = storage_path('app/exports/' . $fileName);
            
            // Create exports directory if it doesn't exist
            if (!is_dir(storage_path('app/exports'))) {
                mkdir(storage_path('app/exports'), 0755, true);
            }

            $phpWord->save($filePath);

            Log::info('Additional quotation exported', [
                'additional_quotation_id' => $id,
                'user_id' => auth()->id(),
                'file' => $fileName,
            ]);

            return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error exporting additional quotation', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Failed to export quotation: ' . $e->getMessage());
        }
    }

    /**
     * Show public access form for additional quotation
     */
    public function showAdditionalPublicAccessForm($token)
    {
        try {
            $additionalQuotation = AdditionalQuotation::where('public_token', $token)->firstOrFail();

            return view('additional-quotation-public', compact('additionalQuotation', 'token'));
        } catch (\Exception $e) {
            Log::warning('Invalid token for additional quotation access', ['token' => substr($token, 0, 8) . '...']);
            abort(404, 'Additional quotation not found');
        }
    }

    /**
     * Validate public access to additional quotation
     */
    public function validateAdditionalPublicAccess($token, Request $request)
    {
        try {
            $additionalQuotation = AdditionalQuotation::where('public_token', $token)->firstOrFail();

            // For basic implementation, just return success
            // In production, you might check IP whitelist, contact email, etc.
            return response()->json([
                'success' => true,
                'message' => 'Access granted',
            ]);
        } catch (\Exception $e) {
            Log::warning('Unauthorized public access attempt', [
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }
    }

    /**
     * Show additional quotation to public user
     */
    public function showAdditionalPublicQuotation($token)
    {
        try {
            $quotation = AdditionalQuotation::with(['materials', 'parentQuotation.client', 'status'])
                ->where('public_token', $token)
                ->firstOrFail();

            return view('public-quotation', [
                'quotation' => $quotation,
                'isAdditional' => true,
                'materials' => $quotation->materials,
            ]);
        } catch (\Exception $e) {
            Log::warning('Error displaying additional quotation publicly', [
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage(),
            ]);

            return redirect('/')->with('error', 'Quotation not found');
        }
    }

    /**
     * Approve additional quotation from public view (using parent token)
     */
    public function approveAdditionalQuotationPublic($token)
    {
        try {
            // First, get the parent quotation by token
            $parentQuotation = Quotation::where('public_token', $token)->firstOrFail();

            // Then get the additional quotation ID from request
            $additionalQuotationId = request()->input('additional_quotation_id');
            if (!$additionalQuotationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Additional quotation ID is required',
                ], 400);
            }

            // Verify the additional quotation belongs to this parent
            $quotation = AdditionalQuotation::where('id', $additionalQuotationId)
                ->where('parent_quotation_id', $parentQuotation->id)
                ->firstOrFail();

            // Update customer approval status
            $quotation->update(['customer_approved' => true]);

            Log::info('Additional quotation approved by customer', [
                'additional_quotation_id' => $quotation->id,
                'parent_quotation_id' => $parentQuotation->id,
                'token' => substr($token, 0, 8) . '...',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your approval has been recorded.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving additional quotation publicly', [
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record approval',
            ], 500);
        }
    }

    /**
     * Export additional quotation publicly
     */
    public function exportAdditionalPublicQuotation($token)
    {
        try {
            $additionalQuotation = AdditionalQuotation::with('materials', 'parentQuotation.client')
                ->where('public_token', $token)
                ->firstOrFail();

            $fileName = 'Additional_Quotation_' . $additionalQuotation->id . '_' . date('Ymd') . '.docx';

            Log::info('Additional quotation exported publicly', [
                'additional_quotation_id' => $additionalQuotation->id,
                'token' => substr($token, 0, 8) . '...',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Export started',
                'file' => $fileName,
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting additional quotation publicly', [
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to export quotation',
            ], 500);
        }
    }
}


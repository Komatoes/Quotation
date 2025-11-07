<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationComment;
use App\Models\QuotationCommentReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotationCommentController extends Controller
{
    /**
     * Fetch all comments for a quotation by ID (admin/internal).
     */
    public function getAdminComments($id)
    {
        $quotation = Quotation::findOrFail($id);
        $comments = QuotationComment::where('quotation_id', $quotation->id)
            ->orderBy('created_at', 'asc')
            ->get();
        return response()->json($comments);
    }
    /**
     * ✅ Load all comments (with replies)
     */
    public function getComments($publicToken)
    {
        $quotation = Quotation::where('public_token', $publicToken)->firstOrFail();

        $comments = QuotationComment::where('quotation_id', $quotation->id)
            ->with('replies')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($comments);
    }

    /**
     * ✅ Customer submits a comment
     */
    public function storePublicComment(Request $request, $publicToken)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $quotation = Quotation::where('public_token', $publicToken)->firstOrFail();

        QuotationComment::create([
            'quotation_id' => $quotation->id,
            'client_id'    => $quotation->client_id,
            'employee_id'  => null,
            'comment'      => $request->comment,
            'sender_type'  => 'customer',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * ✅ Admin submits a new comment
     */
    public function storeAdminComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $quotation = Quotation::findOrFail($id);

        $comment = QuotationComment::create([
            'quotation_id' => $quotation->id,
            'client_id'    => null,
            'employee_id'  => Auth::id(),
            'comment'      => $request->comment,
            'sender_type'  => 'admin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'comment' => $comment
        ]);
    }

    /**
     * ✅ Admin replies to a specific comment
     */
    public function adminReply(Request $request, $id)
    {
        $request->validate([
            'comment_id' => 'required|exists:quotation_comments,id',
            'reply'      => 'required|string|max:1000',
        ]);

        QuotationCommentReply::create([
            'comment_id' => $request->comment_id,
            'employee_id' => Auth::id(), 
            'client_id'   => null,
            'reply'       => $request->reply,
            'sender_type' => 'admin',
        ]);

        return response()->json(['success' => true, 'message' => 'Reply sent']);
    }

    /**
     * ✅ Customer approves quotation
     */
    public function customerApprove($publicToken)
    {
        $quotation = Quotation::where('public_token', $publicToken)->firstOrFail();
        $quotation->update(['customer_approved' => true]);

        return response()->json(['success' => true, 'message' => 'Quotation approved by customer']);
    }

    /**
     * ✅ Admin approves after customer
     */
    public function adminApprove($id)
    {
        $quotation = Quotation::findOrFail($id);

        if (!$quotation->customer_approved) {
            return response()->json(['error' => 'Customer has not approved yet'], 400);
        }

        $quotation->update([
            'provider_approved' => true,
            'status_id'         => 2 // Approved
        ]);

        return response()->json(['success' => true, 'message' => 'Quotation approved by admin']);
    }
}

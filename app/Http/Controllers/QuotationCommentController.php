<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationComment;
use App\Models\QuotationCommentReply;
use App\Helpers\NotificationHelper;
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
            ->with(['replies.nestedReplies'])
            ->orderBy('created_at', 'asc')
            ->get();
        return response()->json($comments);
    }

    /**
     * Load all comments (with replies)
     */
    public function getComments($publicToken)
    {
        $quotation = Quotation::where('public_token', $publicToken)->firstOrFail();

        $comments = QuotationComment::where('quotation_id', $quotation->id)
            ->with(['replies.nestedReplies'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($comments);
    }

    /**
     * Customer submits a comment
     */
    public function storePublicComment(Request $request, $publicToken)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $quotation = Quotation::where('public_token', $publicToken)->firstOrFail();

        // Get session token from request header (sent by client JS)
        $sessionToken = $request->header('X-Session-Token') ?? uniqid();

        $comment = QuotationComment::create([
            'quotation_id' => $quotation->id,
            'user_id'      => null,
            'user_name'    => $quotation->client->first_name . ' ' . $quotation->client->last_name,
            'comment'      => $request->comment,
            'sender_type'  => 'customer',
            'session_token' => $sessionToken,
        ]);

        // Create notification for admin/staff
        NotificationHelper::notifyCommentAdded($comment, $quotation);

        // Optionally eager load relationships if needed for UI
        $comment = QuotationComment::with(['replies.nestedReplies'])->find($comment->id);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'comment' => $comment,
            'session_token' => $sessionToken
        ]);
    }

    /**
     * Admin/Staff submits a new comment
     */
    public function storeAdminComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $quotation = Quotation::findOrFail($id);

        $comment = QuotationComment::create([
            'quotation_id' => $quotation->id,
            'user_id'      => Auth::id(),
            'user_name'    => Auth::user()->name,
            'comment'      => $request->comment,
            'sender_type'  => Auth::user()->hasRole('admin') ? 'admin' : (Auth::user()->hasRole('staff') ? 'staff' : 'customer'),
        ]);

        // Create notification for admin/staff
        NotificationHelper::notifyCommentAdded($comment, $quotation);

        // Reload with relationships
        $comment = QuotationComment::with(['replies.nestedReplies'])->find($comment->id);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'comment' => $comment
        ]);
    }

    /**
     * Update a comment - STRICT OWNERSHIP ONLY
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $comment = QuotationComment::findOrFail($id);

        // STRICT: Only the owner (user_id must match Auth::id()) can edit
        if (!Auth::check() || Auth::id() !== $comment->user_id) {
            return response()->json(['success' => false, 'message' => 'You can only edit your own comments'], 403);
        }

        $comment->update(['comment' => $request->comment]);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully'
        ]);
    }

    /**
     * Delete a comment - STRICT OWNERSHIP ONLY
     */
    public function destroy($id)
    {
        $comment = QuotationComment::findOrFail($id);

        // STRICT: Only the owner (user_id must match Auth::id()) can delete
        if (!Auth::check() || Auth::id() !== $comment->user_id) {
            return response()->json(['success' => false, 'message' => 'You can only delete your own comments'], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }

    /**
     * Add a reply to a comment
     */
    public function storeReply(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $parentComment = QuotationComment::findOrFail($id);

        // Handle both authenticated and unauthenticated users
        if (Auth::check()) {
            // Authenticated user (admin/staff)
            $reply = QuotationCommentReply::create([
                'quotation_comment_id' => $parentComment->id,
                'parent_reply_id'      => null,
                'user_id'              => Auth::id(),
                'user_name'            => Auth::user()->name,
                'comment'              => $request->comment,
                'sender_type'          => Auth::user()->hasRole('admin') ? 'admin' : (Auth::user()->hasRole('staff') ? 'staff' : 'customer'),
            ]);
        } else {
            // Unauthenticated public customer - get their name from quotation client
            $quotation = $parentComment->quotation;
            $reply = QuotationCommentReply::create([
                'quotation_comment_id' => $parentComment->id,
                'parent_reply_id'      => null,
                'user_id'              => null,
                'user_name'            => $quotation->client->first_name . ' ' . $quotation->client->last_name,
                'comment'              => $request->comment,
                'sender_type'          => 'customer',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reply added successfully',
            'reply'   => $reply
        ]);
    }

    /**
     * Add a nested reply (reply to a reply)
     */
    public function storeNestedReply(Request $request, $replyId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $parentReply = QuotationCommentReply::findOrFail($replyId);

        // Handle both authenticated and unauthenticated users
        if (Auth::check()) {
            // Authenticated user (admin/staff)
            $nestedReply = QuotationCommentReply::create([
                'quotation_comment_id' => $parentReply->quotation_comment_id,
                'parent_reply_id'      => $parentReply->id,
                'user_id'              => Auth::id(),
                'user_name'            => Auth::user()->name,
                'comment'              => $request->comment,
                'sender_type'          => Auth::user()->hasRole('admin') ? 'admin' : (Auth::user()->hasRole('staff') ? 'staff' : 'customer'),
            ]);
        } else {
            // Unauthenticated public customer
            $quotation = $parentReply->parentComment->quotation;
            $nestedReply = QuotationCommentReply::create([
                'quotation_comment_id' => $parentReply->quotation_comment_id,
                'parent_reply_id'      => $parentReply->id,
                'user_id'              => null,
                'user_name'            => $quotation->client->first_name . ' ' . $quotation->client->last_name,
                'comment'              => $request->comment,
                'sender_type'          => 'customer',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Nested reply added successfully',
            'reply'   => $nestedReply
        ]);
    }

    /**
     * Public customer updates their comment (by public token)
     * Check: session_token must match comment's session_token
     */
    public function updatePublicComment(Request $request, $token, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        // Verify quotation exists with this token
        $quotation = Quotation::where('public_token', $token)->firstOrFail();

        // Get comment and verify it belongs to this quotation
        $comment = QuotationComment::where('id', $id)
            ->where('quotation_id', $quotation->id)
            ->firstOrFail();

        // Only allow if session token matches. Accept token from header OR cookie for persistence across reloads
        $sessionToken = $request->header('X-Session-Token') ?? $request->cookie('publicCommentSessionToken');
        if (!$sessionToken || $sessionToken !== $comment->session_token) {
            return response()->json(['success' => false, 'message' => 'You can only edit your own comments'], 403);
        }

        $comment->update(['comment' => $request->comment]);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully'
        ]);
    }

    /**
     * Public customer deletes their comment (by public token)
     * Check: session_token must match comment's session_token
     */
    public function destroyPublicComment(Request $request, $token, $id)
    {
        // Verify quotation exists with this token
        $quotation = Quotation::where('public_token', $token)->firstOrFail();

        // Get comment and verify it belongs to this quotation
        $comment = QuotationComment::where('id', $id)
            ->where('quotation_id', $quotation->id)
            ->firstOrFail();

        // Only allow if session token matches. Accept token from header OR cookie for persistence across reloads
        $sessionToken = $request->header('X-Session-Token') ?? $request->cookie('publicCommentSessionToken');
        if (!$sessionToken || $sessionToken !== $comment->session_token) {
            return response()->json(['success' => false, 'message' => 'You can only delete your own comments'], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }

    /**
     * Public customer updates their reply (by public token)
     * Check: session_token must match reply's session_token
     */
    public function updatePublicReply(Request $request, $token, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        // Verify quotation exists with this token
        $quotation = Quotation::where('public_token', $token)->firstOrFail();

        // Get reply and verify it belongs to this quotation
        $reply = QuotationCommentReply::where('id', $id)
            ->whereHas('parentComment', function($q) use ($quotation) {
                $q->where('quotation_id', $quotation->id);
            })
            ->firstOrFail();

        // Only allow if session token matches. Accept token from header OR cookie for persistence across reloads
        $sessionToken = $request->header('X-Session-Token') ?? $request->cookie('publicCommentSessionToken');
        if (!$sessionToken || $sessionToken !== $reply->session_token) {
            return response()->json(['success' => false, 'message' => 'You can only edit your own replies'], 403);
        }

        $reply->update(['comment' => $request->comment]);

        return response()->json([
            'success' => true,
            'message' => 'Reply updated successfully'
        ]);
    }

    /**
     * Public customer deletes their reply (by public token)
     * Check: session_token must match reply's session_token
     */
    public function destroyPublicReply(Request $request, $token, $id)
    {
        // Verify quotation exists with this token
        $quotation = Quotation::where('public_token', $token)->firstOrFail();

        // Get reply and verify it belongs to this quotation
        $reply = QuotationCommentReply::where('id', $id)
            ->whereHas('parentComment', function($q) use ($quotation) {
                $q->where('quotation_id', $quotation->id);
            })
            ->firstOrFail();

        // Only allow if session token matches. Accept token from header OR cookie for persistence across reloads
        $sessionToken = $request->header('X-Session-Token') ?? $request->cookie('publicCommentSessionToken');
        if (!$sessionToken || $sessionToken !== $reply->session_token) {
            return response()->json(['success' => false, 'message' => 'You can only delete your own replies'], 403);
        }

        $reply->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reply deleted successfully'
        ]);
    }

    /**
     * Public customer submits a reply to a comment (by public token)
     */
    public function storePublicReply(Request $request, $token, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        // Verify quotation exists with this token
        $quotation = Quotation::where('public_token', $token)->firstOrFail();
        
        // Verify comment belongs to this quotation
        $parentComment = QuotationComment::where('id', $id)
            ->where('quotation_id', $quotation->id)
            ->firstOrFail();

        // Get session token from request header
        $sessionToken = $request->header('X-Session-Token') ?? uniqid();

        // Create reply with user_id = null (public reply)
        $reply = QuotationCommentReply::create([
            'quotation_comment_id' => $parentComment->id,
            'parent_reply_id'      => null,
            'user_id'              => null,
            'user_name'            => $quotation->client->first_name . ' ' . $quotation->client->last_name,
            'comment'              => $request->comment,
            'sender_type'          => 'customer',
            'session_token'        => $sessionToken,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reply added successfully',
            'reply'   => $reply,
            'session_token' => $sessionToken
        ]);
    }

    /**
     * Public customer submits a nested reply (by public token)
     */
    public function storePublicNestedReply(Request $request, $token, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        // Verify quotation exists with this token
        $quotation = Quotation::where('public_token', $token)->firstOrFail();
        
        // Verify reply belongs to this quotation
        $parentReply = QuotationCommentReply::where('id', $id)
            ->whereHas('parentComment', function($q) use ($quotation) {
                $q->where('quotation_id', $quotation->id);
            })
            ->firstOrFail();

        // Get session token from request header
        $sessionToken = $request->header('X-Session-Token') ?? uniqid();

        // Create nested reply with user_id = null (public reply)
        $nestedReply = QuotationCommentReply::create([
            'quotation_comment_id' => $parentReply->quotation_comment_id,
            'parent_reply_id'      => $parentReply->id,
            'user_id'              => null,
            'user_name'            => $quotation->client->first_name . ' ' . $quotation->client->last_name,
            'comment'              => $request->comment,
            'sender_type'          => 'customer',
            'session_token'        => $sessionToken,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nested reply added successfully',
            'reply'   => $nestedReply,
            'session_token' => $sessionToken
        ]);
    }

    /**
     * Update a reply - STRICT OWNERSHIP ONLY
     */
    public function updateReply(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $reply = QuotationCommentReply::findOrFail($id);

        // STRICT: Only the owner (user_id must match Auth::id()) can edit
        if (!Auth::check() || Auth::id() !== $reply->user_id) {
            return response()->json(['success' => false, 'message' => 'You can only edit your own replies'], 403);
        }

        $reply->update(['comment' => $request->comment]);

        return response()->json([
            'success' => true,
            'message' => 'Reply updated successfully'
        ]);
    }

    /**
     * Delete a reply - STRICT OWNERSHIP ONLY
     */
    public function destroyReply($id)
    {
        $reply = QuotationCommentReply::findOrFail($id);

        // STRICT: Only the owner (user_id must match Auth::id()) can delete
        if (!Auth::check() || Auth::id() !== $reply->user_id) {
            return response()->json(['success' => false, 'message' => 'You can only delete your own replies'], 403);
        }

        $reply->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reply deleted successfully'
        ]);
    }

    /**
     * Customer approves quotation
     */
    public function customerApprove($publicToken)
    {
        $quotation = Quotation::where('public_token', $publicToken)->firstOrFail();
        $quotation->update([
            'customer_approved' => true,
            'approved_by_customer_at' => now()
        ]);

        // ✅ NEW: Create notification for approval
        NotificationHelper::notify(
            auth()->user()->id ?? $quotation->employee_id,
            'customer_approval',
            'Customer Approved Quotation',
            "Customer {$quotation->client->client_name} approved quotation: {$quotation->quotation_number}",
            'Quotation',
            $quotation->id
        );

        return response()->json(['success' => true, 'message' => 'Quotation approved by customer']);
    }

    /**
     * Get comments for additional quotation
     */
    public function getAdditionalComments($id)
    {
        $additionalQuotation = \App\Models\AdditionalQuotation::findOrFail($id);
        // Query using parent_quotation_id and quotation_type='additional' to comply with FK constraint
        $comments = QuotationComment::where('quotation_id', $additionalQuotation->parent_quotation_id)
            ->where('quotation_type', 'additional')
            ->with(['replies.nestedReplies'])
            ->orderBy('created_at', 'asc')
            ->get();
        return response()->json($comments);
    }

    /**
     * Store comment for additional quotation
     */
    public function storeAdditionalComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $additionalQuotation = \App\Models\AdditionalQuotation::findOrFail($id);

        // Store parent quotation ID (not additional quotation ID) to comply with FK constraint
        $comment = QuotationComment::create([
            'quotation_id' => $additionalQuotation->parent_quotation_id,
            'quotation_type' => 'additional',
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'comment' => $request->comment,
            'sender_type' => 'admin',
        ]);

        $comment = QuotationComment::with(['replies.nestedReplies'])->find($comment->id);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'comment' => $comment,
        ]);
    }
}

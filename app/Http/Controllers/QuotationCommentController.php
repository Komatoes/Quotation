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

        $comment = QuotationComment::create([
            'quotation_id' => $quotation->id,
            'user_id'      => null,
            'user_name'    => $quotation->client->first_name . ' ' . $quotation->client->last_name,
            'comment'      => $request->comment,
            'sender_type'  => 'customer',
        ]);

        // Optionally eager load relationships if needed for UI
        $comment = QuotationComment::with(['replies.nestedReplies'])->find($comment->id);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'comment' => $comment
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

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'comment' => $comment
        ]);
    }

    /**
     * Update a comment
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $comment = QuotationComment::findOrFail($id);

        // Allow: public user (user_id null), or owner (admin/staff)
        if (Auth::check()) {
            if (Auth::id() !== $comment->user_id && $comment->user_id !== null) {
                return response()->json(['success' => false, 'message' => 'You can only edit your own or public comments'], 403);
            }
        } else {
            if ($comment->user_id !== null) {
                return response()->json(['success' => false, 'message' => 'You can only edit your own public comments'], 403);
            }
        }

        $comment->update(['comment' => $request->comment]);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully'
        ]);
    }

    /**
     * Delete a comment
     */
    public function destroy($id)
    {
        $comment = QuotationComment::findOrFail($id);

        // Allow: public user (user_id null), or owner (admin/staff)
        if (Auth::check()) {
            if (Auth::id() !== $comment->user_id && $comment->user_id !== null) {
                return response()->json(['success' => false, 'message' => 'You can only delete your own or public comments'], 403);
            }
        } else {
            if ($comment->user_id !== null) {
                return response()->json(['success' => false, 'message' => 'You can only delete your own public comments'], 403);
            }
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
     */
    public function updatePublicComment(Request $request, $token, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        // Verify quotation exists with this token
        $quotation = Quotation::where('public_token', $token)->firstOrFail();
        
        // Verify comment belongs to this quotation and is public (user_id = null)
        $comment = QuotationComment::where('id', $id)
            ->where('quotation_id', $quotation->id)
            ->where('user_id', null)
            ->firstOrFail();

        $comment->update(['comment' => $request->comment]);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully'
        ]);
    }

    /**
     * Public customer deletes their comment (by public token)
     */
    public function destroyPublicComment($token, $id)
    {
        // Verify quotation exists with this token
        $quotation = Quotation::where('public_token', $token)->firstOrFail();
        
        // Verify comment belongs to this quotation and is public (user_id = null)
        $comment = QuotationComment::where('id', $id)
            ->where('quotation_id', $quotation->id)
            ->where('user_id', null)
            ->firstOrFail();

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }

    /**
     * Public customer updates their reply (by public token)
     */
    public function updatePublicReply(Request $request, $token, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        // Verify quotation exists with this token
        $quotation = Quotation::where('public_token', $token)->firstOrFail();
        
        // Verify reply belongs to this quotation and is public (user_id = null)
        $reply = QuotationCommentReply::where('id', $id)
            ->where('user_id', null)
            ->whereHas('parentComment', function($q) use ($quotation) {
                $q->where('quotation_id', $quotation->id);
            })
            ->firstOrFail();

        $reply->update(['comment' => $request->comment]);

        return response()->json([
            'success' => true,
            'message' => 'Reply updated successfully'
        ]);
    }

    /**
     * Public customer deletes their reply (by public token)
     */
    public function destroyPublicReply($token, $id)
    {
        // Verify quotation exists with this token
        $quotation = Quotation::where('public_token', $token)->firstOrFail();
        
        // Verify reply belongs to this quotation and is public (user_id = null)
        $reply = QuotationCommentReply::where('id', $id)
            ->where('user_id', null)
            ->whereHas('parentComment', function($q) use ($quotation) {
                $q->where('quotation_id', $quotation->id);
            })
            ->firstOrFail();

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

        // Create reply with user_id = null (public reply)
        $reply = QuotationCommentReply::create([
            'quotation_comment_id' => $parentComment->id,
            'parent_reply_id'      => null,
            'user_id'              => null,
            'user_name'            => $quotation->client->first_name . ' ' . $quotation->client->last_name,
            'comment'              => $request->comment,
            'sender_type'          => 'customer',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reply added successfully',
            'reply'   => $reply
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

        // Create nested reply with user_id = null (public reply)
        $nestedReply = QuotationCommentReply::create([
            'quotation_comment_id' => $parentReply->quotation_comment_id,
            'parent_reply_id'      => $parentReply->id,
            'user_id'              => null,
            'user_name'            => $quotation->client->first_name . ' ' . $quotation->client->last_name,
            'comment'              => $request->comment,
            'sender_type'          => 'customer',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nested reply added successfully',
            'reply'   => $nestedReply
        ]);
    }

    /**
     * Update a reply
     */
    public function updateReply(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $reply = QuotationCommentReply::findOrFail($id);

        // Check authorization
        if (Auth::check()) {
            // Authenticated user (admin/staff) - can edit their own reply OR public replies
            if (Auth::id() !== $reply->user_id && $reply->user_id !== null) {
                return response()->json(['success' => false, 'message' => 'You can only edit your own or public replies'], 403);
            }
        } else {
            // Unauthenticated public customer - can only edit if reply has no user_id
            if ($reply->user_id !== null) {
                return response()->json(['success' => false, 'message' => 'You can only edit your own public replies'], 403);
            }
        }

        $reply->update(['comment' => $request->comment]);

        return response()->json([
            'success' => true,
            'message' => 'Reply updated successfully'
        ]);
    }

    /**
     * Delete a reply
     */
    public function destroyReply($id)
    {
        $reply = QuotationCommentReply::findOrFail($id);

        // Check authorization
        if (Auth::check()) {
            // Authenticated user (admin/staff) - can delete their own reply OR public replies
            if (Auth::id() !== $reply->user_id && $reply->user_id !== null) {
                return response()->json(['success' => false, 'message' => 'You can only delete your own or public replies'], 403);
            }
        } else {
            // Unauthenticated public customer - can only delete if reply has no user_id
            if ($reply->user_id !== null) {
                return response()->json(['success' => false, 'message' => 'You can only delete your own public replies'], 403);
            }
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
        $quotation->update(['customer_approved' => true]);

        return response()->json(['success' => true, 'message' => 'Quotation approved by customer']);
    }
}

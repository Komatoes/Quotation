<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuotationCommentAdminController extends Controller
{
    protected $base;

    public function __construct()
    {
        // Delegate to existing controller which contains the logic
        $this->base = new QuotationCommentController();
    }

    public function getAdminComments($id)
    {
        return $this->base->getAdminComments($id);
    }

    public function storeAdminComment(Request $request, $id)
    {
        return $this->base->storeAdminComment($request, $id);
    }

    // Additional quotations comments
    public function getAdditionalComments($id)
    {
        return $this->base->getAdditionalComments($id);
    }

    public function storeAdditionalComment(Request $request, $id)
    {
        return $this->base->storeAdditionalComment($request, $id);
    }

    // Authenticated edit/delete
    public function update(Request $request, $id)
    {
        return $this->base->update($request, $id);
    }

    public function destroy($id)
    {
        return $this->base->destroy($id);
    }

    // Replies for admin
    public function storeReply(Request $request, $id)
    {
        return $this->base->storeReply($request, $id);
    }

    public function storeNestedReply(Request $request, $replyId)
    {
        return $this->base->storeNestedReply($request, $replyId);
    }

    public function updateReply(Request $request, $id)
    {
        return $this->base->updateReply($request, $id);
    }

    public function destroyReply($id)
    {
        return $this->base->destroyReply($id);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuotationCommentPublicController extends Controller
{
    protected $base;

    public function __construct()
    {
        // Delegate to existing controller which contains the logic
        $this->base = new QuotationCommentController();
    }

    public function getComments($publicToken)
    {
        return $this->base->getComments($publicToken);
    }

    public function storePublicComment(Request $request, $publicToken)
    {
        return $this->base->storePublicComment($request, $publicToken);
    }

    public function updatePublicComment(Request $request, $token, $id)
    {
        return $this->base->updatePublicComment($request, $token, $id);
    }

    public function destroyPublicComment(Request $request, $token, $id)
    {
        return $this->base->destroyPublicComment($request, $token, $id);
    }

    public function storePublicReply(Request $request, $token, $id)
    {
        return $this->base->storePublicReply($request, $token, $id);
    }

    public function storePublicNestedReply(Request $request, $token, $id)
    {
        return $this->base->storePublicNestedReply($request, $token, $id);
    }

    public function updatePublicReply(Request $request, $token, $id)
    {
        return $this->base->updatePublicReply($request, $token, $id);
    }

    public function destroyPublicReply(Request $request, $token, $id)
    {
        return $this->base->destroyPublicReply($request, $token, $id);
    }

    public function customerApprove($publicToken)
    {
        return $this->base->customerApprove($publicToken);
    }
}

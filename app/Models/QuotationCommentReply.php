<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationCommentReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_comment_id',
        'parent_reply_id',
        'user_id',
        'user_name',
        'sender_type',
        'comment',
    ];

    /**
     * Get the parent comment
     */
    public function parentComment()
    {
        return $this->belongsTo(QuotationComment::class, 'quotation_comment_id');
    }

    /**
     * Get the parent reply (if this is a nested reply)
     */
    public function parentReply()
    {
        return $this->belongsTo(QuotationCommentReply::class, 'parent_reply_id');
    }

    /**
     * Get nested replies (replies to this reply)
     */
    public function nestedReplies()
    {
        return $this->hasMany(QuotationCommentReply::class, 'parent_reply_id')->orderBy('created_at', 'asc');
    }

    /**
     * Get the user who posted the reply
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

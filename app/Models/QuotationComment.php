<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationComment extends Model
{
    protected $fillable = [
        'quotation_id',
        'user_id',
        'user_name',
        'comment',
        'sender_type' // differentiate between customer/admin
    ];

    public $timestamps = true;

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A comment can have many replies
     */
    public function replies()
    {
        return $this->hasMany(QuotationCommentReply::class, 'quotation_comment_id')
            ->whereNull('parent_reply_id') // Only get top-level replies
            ->with('nestedReplies') // Eager load nested replies
            ->orderBy('created_at', 'asc');
    }
}

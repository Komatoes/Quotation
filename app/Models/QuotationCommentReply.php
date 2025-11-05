<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationCommentReply extends Model
{
    protected $fillable = [
        'comment_id',
        'employee_id',
        'client_id',
        'reply',
        'sender_type'
    ];

    public function comment()
    {
        return $this->belongsTo(QuotationComment::class, 'comment_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}

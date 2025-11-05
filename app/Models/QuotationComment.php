<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationComment extends Model
{
    protected $fillable = [
        'quotation_id',
        'client_id',
        'employee_id',
        'comment',
        'sender_type' // we add this to differentiate between customer/admin
    ];

    public $timestamps = true;

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // ✅ A comment can have many replies
    public function replies()
    {
        return $this->hasMany(QuotationCommentReply::class, 'comment_id');
    }
}

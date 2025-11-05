<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationComment extends Model
{
    protected $fillable = [
        'quotation_id',
        'comment',
        'sender_type',
        'is_read'
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
}
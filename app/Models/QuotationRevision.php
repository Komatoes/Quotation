<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationRevision extends Model
{
    protected $fillable = [
        'quotation_id',
        'quotation_type',
        'old_data',
        'reason',
        'change_reason',
        'version',
        'created_by',
    ];

    protected $casts = [
        'old_data' => 'array',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
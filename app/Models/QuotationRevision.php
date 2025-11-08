<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationRevision extends Model
{
    protected $fillable = [
        'quotation_id',
        'old_data',
        'reason',
        'version',
    ];

    protected $casts = [
        'old_data' => 'array',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
}
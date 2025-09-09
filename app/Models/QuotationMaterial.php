<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'material_id',
        'unit_cost',
        'quantity',
    ];
}
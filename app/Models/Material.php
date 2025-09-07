<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * These correspond to your materials table columns.
     */
    protected $fillable = [
        'name',
        'description',
        'quantity',
        'unit_price',
        'unit',
    ];

    /**
     * Optionally, cast attributes for easier use.
     */
    protected $casts = [
        'quantity'   => 'integer',
        'unit_price' => 'decimal:2',
    ];
}

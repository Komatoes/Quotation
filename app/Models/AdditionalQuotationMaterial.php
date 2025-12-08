<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdditionalQuotationMaterial extends Model
{
    protected $table = 'additional_quotation_materials';

    protected $fillable = [
        'additional_quotation_id',
        'material_id',
        'quantity',
        'unit_cost',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the additional quotation this material belongs to
     */
    public function additionalQuotation(): BelongsTo
    {
        return $this->belongsTo(AdditionalQuotation::class);
    }

    /**
     * Get the material details
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Get the line total for this material
     */
    public function getLineTotalAttribute(): float
    {
        return (float) ($this->unit_cost * $this->quantity);
    }

    /**
     * Get formatted unit cost
     */
    public function getFormattedUnitCostAttribute(): string
    {
        return number_format($this->unit_cost, 2);
    }

    /**
     * Get formatted line total
     */
    public function getFormattedLineTotalAttribute(): string
    {
        return number_format($this->line_total, 2);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdditionalQuotation extends Model
{
    protected $table = 'additional_quotations';

    protected $fillable = [
        'parent_quotation_id',
        'subject',
        'description',
        'status_id',
        'progress',
        'customer_approved',
        'rejection_reason',
        'labor_fee',
        'delivery_fee',
        'public_token',
    ];

    protected $casts = [
        'status_id' => 'integer',
        'progress' => 'integer',
        'customer_approved' => 'boolean',
        'labor_fee' => 'float',
        'delivery_fee' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the parent quotation this additional quotation belongs to
     */
    public function parentQuotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'parent_quotation_id');
    }

    /**
     * Get the status of this additional quotation
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(QuotationStatus::class, 'status_id');
    }

    /**
     * Get all materials in this additional quotation (many-to-many)
     */
    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(
            Material::class,
            'additional_quotation_materials',
            'additional_quotation_id',
            'material_id'
        )->withPivot('quantity', 'unit_cost', 'id');
    }

    /**
     * Get all comments for this additional quotation
     */
    public function comments(): HasMany
    {
        return $this->hasMany(QuotationComment::class, 'quotation_id')
                    ->where('quotation_type', 'additional');
    }

    /**
     * Calculate total material cost for this additional quotation
     */
    public function getMaterialTotal(): float
    {
        return $this->materials->sum(fn($m) => ($m->pivot->unit_cost ?? $m->unit_price ?? 0) * ($m->pivot->quantity ?? 0));
    }

    /**
     * Get grand total (materials + labor + delivery)
     */
    public function getGrandTotal(): float
    {
        return $this->getMaterialTotal() + ($this->labor_fee ?? 0) + ($this->delivery_fee ?? 0);
    }

    /**
     * Get status name
     */
    public function getStatusNameAttribute(): string
    {
        return $this->status?->status_name ?? 'Unknown';
    }

    /**
     * Get formatted created date
     */
    public function getFormattedCreatedDate(): string
    {
        // Format using application timezone to avoid off-by-one day issues
        return $this->created_at->setTimezone(config('app.timezone'))->format('M d, Y');
    }

    /**
     * Check if this additional quotation is approved
     */
    public function isApproved(): bool
    {
        return $this->progress >= 100;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'description',
        'employee_id',
        'client_id',
        'status_id',
        'labor_fee',
        'delivery_fee',
        'latest_progress',
        'public_token',
        'customer_approved',
        'provider_approved',
        'feedback_status',
        'rejection_reason',
        'rejected_at',
        'rejected_by',
        'parent_quotation_id',
        'quotation_type',
        'contract_subject',
        'project_start_date',
        'project_end_date',
        'with_contract'
    ];

    protected $casts = [
        'rejected_at' => 'datetime',
        'customer_approved' => 'boolean',
        'provider_approved' => 'boolean',
        'with_contract' => 'boolean',
        'project_start_date' => 'date',
        'project_end_date' => 'date',
    ];

    /**
     * Check if both customer and provider have approved the quotation.
     *
     * @return bool
     */
    public function isFullyApproved(): bool
    {
        return $this->customer_approved && $this->provider_approved;
    }

    // Relation to client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relation to employee (user who created it)
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    // Relation to status
    public function status()
    {
        return $this->belongsTo(QuotationStatus::class, 'status_id');
    }

    // Relation to materials (many-to-many via quotation_materials)
    public function materials()
    {
        return $this->belongsToMany(Material::class, 'quotation_materials')
                    ->withPivot('quantity', 'unit_cost', 'id'); 
    }

    public function progressReports()
    {
        return $this->hasMany(ProjectReport::class);
    }

    public function revisions()
    {
        return $this->hasMany(QuotationRevision::class);
    }

    public function comments()
    {
        return $this->hasMany(QuotationComment::class);
    }

    /**
     * Get the user who rejected this quotation
     */
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get the parent quotation if this is an add-on
     */
    public function parentQuotation()
    {
        return $this->belongsTo(Quotation::class, 'parent_quotation_id');
    }

    /**
     * Get all linked/add-on quotations for this quotation (legacy - for backwards compatibility)
     */
    public function linkedQuotations()
    {
        return $this->hasMany(Quotation::class, 'parent_quotation_id');
    }

    /**
     * Get all additional quotations nested under this quotation (NEW)
     */
    public function additionalQuotations()
    {
        return $this->hasMany(AdditionalQuotation::class, 'parent_quotation_id');
    }

    /**
     * Check if this quotation has been rejected
     */
    public function isRejected(): bool
    {
        return !is_null($this->rejected_at);
    }

    /**
     * Reject the quotation with a reason
     */
    public function reject(string $reason, int $rejectedById): void
    {
        $this->update([
            'rejection_reason' => $reason,
            'rejected_at' => now(),
            'rejected_by' => $rejectedById,
        ]);
    }

    /**
     * Get all quotations linked to this one (children + parent)
     */
    public function getAllLinkedQuotations()
    {
        $linked = collect([$this]);
        
        // Add parent if exists
        if ($this->parent_quotation_id) {
            $linked->push($this->parentQuotation);
        }
        
        // Add all children
        $linked = $linked->merge($this->linkedQuotations);
        
        return $linked;
    }

    /**
     * Get total material cost from parent quotation only
     */
    public function getParentMaterialTotal(): float
    {
        return $this->materials->sum(fn($m) => $m->unit_price * ($m->pivot->quantity ?? 0));
    }

    /**
     * Get total material cost from all additional quotations
     */
    public function getAdditionalMaterialTotal(): float
    {
        return $this->additionalQuotations->sum(fn($additional) => $additional->getMaterialTotal());
    }

    /**
     * Get combined material total (parent + all additional quotations)
     */
    public function getCombinedMaterialTotal(): float
    {
        return $this->getParentMaterialTotal() + $this->getAdditionalMaterialTotal();
    }

    /**
     * Get grand total including all materials from parent and additional quotations plus fees
     * Fees (labor_fee, delivery_fee) are applied ONCE at parent level
     */
    public function getGrandTotalWithChildren(): float
    {
        return $this->getCombinedMaterialTotal() + ($this->labor_fee ?? 0) + ($this->delivery_fee ?? 0);
    }

    /**
     * Get all materials from parent and additional quotations as a collection
     */
    public function getAllMaterials()
    {
        // Parent materials
        $parentMaterials = $this->materials->map(fn($m) => [
            'type' => 'parent',
            'material_id' => $m->id,
            'name' => $m->name,
            'quantity' => $m->pivot->quantity ?? 0,
            'unit_cost' => $m->unit_price,
            'line_total' => $m->unit_price * ($m->pivot->quantity ?? 0),
        ]);

        // Additional quotation materials
        $additionalMaterials = $this->additionalQuotations->flatMap(fn($child) => 
            $child->materials->map(fn($m) => [
                'type' => 'additional',
                'additional_quotation_id' => $child->id,
                'additional_quotation_subject' => $child->subject,
                'material_id' => $m->material_id,
                'name' => $m->material->name,
                'quantity' => $m->quantity,
                'unit_cost' => $m->unit_cost,
                'line_total' => $m->unit_cost * $m->quantity,
            ])
        );

        return $parentMaterials->concat($additionalMaterials);
    }

    /**
     * Get combined progress (weighted average of parent + all additional quotations)
     */
    public function getCombinedProgress(): int
    {
        if ($this->additionalQuotations->isEmpty()) {
            return $this->latest_progress ?? 0;
        }

        $allProgress = collect([$this->latest_progress ?? 0])
            ->merge($this->additionalQuotations->pluck('progress'));

        return (int) round($allProgress->average());
    }
}

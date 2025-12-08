<?php

namespace App\Observers;

use App\Helpers\SystemLogHelper;
use App\Models\Quotation;

class QuotationObserver
{
    /**
     * Handle the Quotation "created" event.
     */
    public function created(Quotation $quotation): void
    {
        SystemLogHelper::logQuotation(
            action: 'created',
            description: "Quotation created: {$quotation->quotation_number}",
            quotationId: $quotation->id,
            changes: ['created_at' => $quotation->created_at]
        );
    }

    /**
     * Handle the Quotation "updated" event.
     */
    public function updated(Quotation $quotation): void
    {
        // Get only changed attributes
        $changes = $quotation->getChanges();
        
        // Don't log if only updated_at changed
        if (count($changes) === 1 && isset($changes['updated_at'])) {
            return;
        }

        SystemLogHelper::logQuotation(
            action: 'updated',
            description: "Quotation updated: {$quotation->quotation_number}",
            quotationId: $quotation->id,
            changes: [
                'before' => $quotation->getOriginal(),
                'after' => $changes,
            ]
        );
    }

    /**
     * Handle the Quotation "deleted" event.
     */
    public function deleted(Quotation $quotation): void
    {
        SystemLogHelper::logQuotation(
            action: 'deleted',
            description: "Quotation deleted: {$quotation->quotation_number}",
            quotationId: $quotation->id,
            changes: ['deleted_at' => now()]
        );
    }

    /**
     * Handle the Quotation "restored" event.
     */
    public function restored(Quotation $quotation): void
    {
        SystemLogHelper::logQuotation(
            action: 'restored',
            description: "Quotation restored: {$quotation->quotation_number}",
            quotationId: $quotation->id
        );
    }

    /**
     * Handle the Quotation "force deleted" event.
     */
    public function forceDeleted(Quotation $quotation): void
    {
        SystemLogHelper::logQuotation(
            action: 'force_deleted',
            description: "Quotation permanently deleted: {$quotation->quotation_number}",
            quotationId: $quotation->id
        );
    }
}

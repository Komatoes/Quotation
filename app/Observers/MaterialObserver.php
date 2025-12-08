<?php

namespace App\Observers;

use App\Helpers\SystemLogHelper;
use App\Models\Material;

class MaterialObserver
{
    /**
     * Handle the Material "created" event.
     */
    public function created(Material $material): void
    {
        SystemLogHelper::log(
            action: 'created',
            description: "Material created: {$material->material_name}",
            model: 'App\Models\Material',
            modelId: $material->id,
            changes: ['created_at' => $material->created_at]
        );
    }

    /**
     * Handle the Material "updated" event.
     */
    public function updated(Material $material): void
    {
        // Get only changed attributes
        $changes = $material->getChanges();
        
        // Don't log if only updated_at changed
        if (count($changes) === 1 && isset($changes['updated_at'])) {
            return;
        }

        SystemLogHelper::log(
            action: 'updated',
            description: "Material updated: {$material->material_name}",
            model: 'App\Models\Material',
            modelId: $material->id,
            changes: [
                'before' => $material->getOriginal(),
                'after' => $changes,
            ]
        );
    }

    /**
     * Handle the Material "deleted" event.
     */
    public function deleted(Material $material): void
    {
        SystemLogHelper::log(
            action: 'deleted',
            description: "Material deleted: {$material->material_name}",
            model: 'App\Models\Material',
            modelId: $material->id,
            changes: ['deleted_at' => now()]
        );
    }

    /**
     * Handle the Material "restored" event.
     */
    public function restored(Material $material): void
    {
        SystemLogHelper::log(
            action: 'restored',
            description: "Material restored: {$material->material_name}",
            model: 'App\Models\Material',
            modelId: $material->id
        );
    }

    /**
     * Handle the Material "force deleted" event.
     */
    public function forceDeleted(Material $material): void
    {
        SystemLogHelper::log(
            action: 'force_deleted',
            description: "Material permanently deleted: {$material->material_name}",
            model: 'App\Models\Material',
            modelId: $material->id
        );
    }
}

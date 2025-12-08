<?php

namespace App\Observers;

use App\Helpers\SystemLogHelper;
use App\Models\Client;

class ClientObserver
{
    /**
     * Handle the Client "created" event.
     */
    public function created(Client $client): void
    {
        SystemLogHelper::log(
            action: 'created',
            description: "Client created: {$client->client_name}",
            model: 'App\Models\Client',
            modelId: $client->id,
            changes: ['created_at' => $client->created_at]
        );
    }

    /**
     * Handle the Client "updated" event.
     */
    public function updated(Client $client): void
    {
        // Get only changed attributes
        $changes = $client->getChanges();
        
        // Don't log if only updated_at changed
        if (count($changes) === 1 && isset($changes['updated_at'])) {
            return;
        }

        SystemLogHelper::log(
            action: 'updated',
            description: "Client updated: {$client->client_name}",
            model: 'App\Models\Client',
            modelId: $client->id,
            changes: [
                'before' => $client->getOriginal(),
                'after' => $changes,
            ]
        );
    }

    /**
     * Handle the Client "deleted" event.
     */
    public function deleted(Client $client): void
    {
        SystemLogHelper::log(
            action: 'deleted',
            description: "Client deleted: {$client->client_name}",
            model: 'App\Models\Client',
            modelId: $client->id,
            changes: ['deleted_at' => now()]
        );
    }

    /**
     * Handle the Client "restored" event.
     */
    public function restored(Client $client): void
    {
        SystemLogHelper::log(
            action: 'restored',
            description: "Client restored: {$client->client_name}",
            model: 'App\Models\Client',
            modelId: $client->id
        );
    }

    /**
     * Handle the Client "force deleted" event.
     */
    public function forceDeleted(Client $client): void
    {
        SystemLogHelper::log(
            action: 'force_deleted',
            description: "Client permanently deleted: {$client->client_name}",
            model: 'App\Models\Client',
            modelId: $client->id
        );
    }
}

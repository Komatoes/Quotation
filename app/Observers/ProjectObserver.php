<?php

namespace App\Observers;

use App\Helpers\SystemLogHelper;
use App\Models\Project;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        SystemLogHelper::logProject(
            action: 'created',
            description: "Project created: {$project->project_name}",
            projectId: $project->id,
            changes: ['created_at' => $project->created_at]
        );
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        // Get only changed attributes
        $changes = $project->getChanges();
        
        // Don't log if only updated_at changed
        if (count($changes) === 1 && isset($changes['updated_at'])) {
            return;
        }

        SystemLogHelper::logProject(
            action: 'updated',
            description: "Project updated: {$project->project_name}",
            projectId: $project->id,
            changes: [
                'before' => $project->getOriginal(),
                'after' => $changes,
            ]
        );
    }

    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        SystemLogHelper::logProject(
            action: 'deleted',
            description: "Project deleted: {$project->project_name}",
            projectId: $project->id,
            changes: ['deleted_at' => now()]
        );
    }

    /**
     * Handle the Project "restored" event.
     */
    public function restored(Project $project): void
    {
        SystemLogHelper::logProject(
            action: 'restored',
            description: "Project restored: {$project->project_name}",
            projectId: $project->id
        );
    }

    /**
     * Handle the Project "force deleted" event.
     */
    public function forceDeleted(Project $project): void
    {
        SystemLogHelper::logProject(
            action: 'force_deleted',
            description: "Project permanently deleted: {$project->project_name}",
            projectId: $project->id
        );
    }
}

<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;

class NotificationHelper
{
    /**
     * Create a notification for a user
     */
    public static function notify($userId, $type, $title, $message, $relatedModel = null, $relatedId = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'related_model' => $relatedModel,
            'related_id' => $relatedId,
        ]);
    }

    /**
     * Notify admin when customer adds a comment
     */
    public static function notifyCommentAdded($comment, $quotation)
    {
        $admins = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'staff']);
        })->get();

        foreach ($admins as $admin) {
            self::notify(
                $admin->id,
                'comment',
                'New Comment',
                "Customer added a comment on quotation: {$quotation->quotation_number}",
                'Quotation',
                $quotation->id
            );
        }
    }

    /**
     * Notify admin when quotation is approved
     */
    public static function notifyQuotationApproved($quotation)
    {
        $admins = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'staff']);
        })->get();

        foreach ($admins as $admin) {
            self::notify(
                $admin->id,
                'approval',
                'Quotation Approved',
                "Quotation {$quotation->quotation_number} has been approved by {$quotation->client->client_name}",
                'Quotation',
                $quotation->id
            );
        }
    }

    /**
     * Notify admin when quotation is rejected
     */
    public static function notifyQuotationRejected($quotation)
    {
        $admins = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'staff']);
        })->get();

        foreach ($admins as $admin) {
            self::notify(
                $admin->id,
                'rejection',
                'Quotation Rejected',
                "Quotation {$quotation->quotation_number} has been rejected",
                'Quotation',
                $quotation->id
            );
        }
    }

    /**
     * Notify admin on project status change
     */
    public static function notifyProjectStatusChange($project, $oldStatus, $newStatus)
    {
        $admins = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'staff']);
        })->get();

        foreach ($admins as $admin) {
            self::notify(
                $admin->id,
                'status_change',
                'Project Status Updated',
                "Project {$project->project_name} status changed from {$oldStatus} to {$newStatus}",
                'Project',
                $project->id
            );
        }
    }

    /**
     * Notify admin on progress report update
     */
    public static function notifyProgressUpdate($project, $percentage)
    {
        $admins = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'staff']);
        })->get();

        foreach ($admins as $admin) {
            self::notify(
                $admin->id,
                'project_update',
                'Progress Update',
                "Project {$project->project_name} progress updated to {$percentage}%",
                'Project',
                $project->id
            );
        }
    }
}

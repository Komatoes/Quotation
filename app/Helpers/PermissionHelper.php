<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    /**
     * Check if user is admin
     */
    public static function isAdmin()
    {
        return Auth::check() && Auth::user()->hasRole('admin');
    }

    /**
     * Check if user is staff
     */
    public static function isStaff()
    {
        return Auth::check() && Auth::user()->hasRole('staff');
    }

    /**
     * Check if user can view drafts (Admin only)
     */
    public static function canViewDrafts()
    {
        return Auth::check() && Auth::user()->can('view_drafts');
    }

    /**
     * Check if user can view materials (Admin only)
     */
    public static function canViewMaterials()
    {
        return Auth::check() && Auth::user()->can('view_materials');
    }

    /**
     * Check if user can view prices (Admin only)
     */
    public static function canViewPrices()
    {
        return Auth::check() && Auth::user()->can('view_prices');
    }

    /**
     * Check if user can edit prices (Admin only)
     */
    public static function canEditPrices()
    {
        return Auth::check() && Auth::user()->can('edit_prices');
    }

    /**
     * Check if user can manage fees (Admin only)
     */
    public static function canManageFees()
    {
        return Auth::check() && Auth::user()->can('manage_fees');
    }

    /**
     * Check if user can view approved projects (Both)
     */
    public static function canViewApprovedProjects()
    {
        return Auth::check() && Auth::user()->can('view_approved_projects');
    }

    /**
     * Check if user can view rejected projects (Both)
     */
    public static function canViewRejectedProjects()
    {
        return Auth::check() && Auth::user()->can('view_rejected_projects');
    }

    /**
     * Check if user can view completed projects (Both)
     */
    public static function canViewCompletedProjects()
    {
        return Auth::check() && Auth::user()->can('view_completed_projects');
    }

    /**
     * Check if user can create progress reports (Staff + Admin)
     */
    public static function canCreateProgressReports()
    {
        return Auth::check() && Auth::user()->can('create_progress_report');
    }

    /**
     * Check if user can create comments (Both)
     */
    public static function canComment()
    {
        return Auth::check() && Auth::user()->can('create_comment');
    }

    /**
     * Check if user can view revision history (Both)
     */
    public static function canViewRevisions()
    {
        return Auth::check() && Auth::user()->can('view_revision_history');
    }

    /**
     * Check if user can manage users (Admin only)
     */
    public static function canManageUsers()
    {
        return Auth::check() && Auth::user()->can('manage_users');
    }
}

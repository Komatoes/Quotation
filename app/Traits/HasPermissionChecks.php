<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasPermissionChecks
{
    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return Auth::check() && Auth::user()->hasRole('admin');
    }

    /**
     * Check if user is staff
     */
    public function isStaff()
    {
        return Auth::check() && Auth::user()->hasRole('staff');
    }

    /**
     * Check if user can view drafts
     */
    public function canViewDrafts()
    {
        return Auth::check() && Auth::user()->can('view_drafts');
    }

    /**
     * Check if user can view materials
     */
    public function canViewMaterials()
    {
        return Auth::check() && Auth::user()->can('view_materials');
    }

    /**
     * Check if user can view prices
     */
    public function canViewPrices()
    {
        return Auth::check() && Auth::user()->can('view_prices');
    }

    /**
     * Check if user can manage fees
     */
    public function canManageFees()
    {
        return Auth::check() && Auth::user()->can('manage_fees');
    }

    /**
     * Check if user can create progress reports
     */
    public function canCreateProgressReports()
    {
        return Auth::check() && Auth::user()->can('create_progress_report');
    }

    /**
     * Check if user can comment
     */
    public function canComment()
    {
        return Auth::check() && Auth::user()->can('create_comment');
    }
}

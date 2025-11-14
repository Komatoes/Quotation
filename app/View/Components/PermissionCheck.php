<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;

class PermissionCheck extends Component
{
    public $isAdmin;
    public $isStaff;
    public $canViewMaterials;
    public $canViewPrices;
    public $canManageFees;
    public $canCreateProgressReport;

    public function __construct()
    {
        $user = Auth::user();
        
        if ($user) {
            $this->isAdmin = $user->hasRole('admin');
            $this->isStaff = $user->hasRole('staff');
            $this->canViewMaterials = $user->can('view_materials');
            $this->canViewPrices = $user->can('view_prices');
            $this->canManageFees = $user->can('manage_fees');
            $this->canCreateProgressReport = $user->can('create_progress_report');
        } else {
            $this->isAdmin = false;
            $this->isStaff = false;
            $this->canViewMaterials = false;
            $this->canViewPrices = false;
            $this->canManageFees = false;
            $this->canCreateProgressReport = false;
        }
    }

    public function render()
    {
        return view('components.permission-check');
    }
}

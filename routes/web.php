<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ProjectReportController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QuotationMaterialController;
use App\Http\Controllers\QuotationExportController;
use App\Http\Controllers\QuotationCommentController;
use App\Http\Controllers\QuotationCommentPublicController;
use App\Http\Controllers\QuotationCommentAdminController;
use App\Http\Controllers\BackupManagementController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ---------------------------------------------------------------------------
// Root
// ---------------------------------------------------------------------------

Route::get('/', function () {
    return redirect()->route('login');
})->middleware('guest');

// ---------------------------------------------------------------------------
// Dashboard / Reports (Requires Auth)
// ---------------------------------------------------------------------------

Route::middleware(['auth', 'role:admin|staff'])->group(function () {
    Route::get('/dashboard', [QuotationController::class, 'viewHome'])->name('dashboard');
    Route::get('/view-report/{id}', [QuotationController::class, 'viewReport'])->name('report');
});

// ---------------------------------------------------------------------------
// PUBLIC QUOTATION ROUTES (no login needed)
// ---------------------------------------------------------------------------

Route::prefix('quotation/public')->group(function () {
    Route::get('/{token}', [QuotationController::class, 'showPublicAccessForm'])->name('quotation.public.form');
    Route::post('/{token}/validate', [QuotationController::class, 'validatePublicAccess'])->name('quotation.public.validate');
    Route::get('/{token}/view', [QuotationController::class, 'showPublicQuotation'])->name('quotation.public.view');

    // 💬 Comments (public)
    Route::post('/{token}/comment', [QuotationCommentPublicController::class, 'storePublicComment'])->name('quotation.comment.submit');
    Route::get('/{token}/comments', [QuotationCommentPublicController::class, 'getComments'])->name('quotation.public.comments');
    Route::put('/{token}/comments/{id}', [QuotationCommentPublicController::class, 'updatePublicComment'])->name('quotation.public.comment.update');
    Route::delete('/{token}/comments/{id}', [QuotationCommentPublicController::class, 'destroyPublicComment'])->name('quotation.public.comment.destroy');
    
    // 💬 Replies (public) - NO AUTH, uses token for context
    Route::post('/{token}/comments/{id}/reply', [QuotationCommentPublicController::class, 'storePublicReply'])->name('quotation.public.reply');
    Route::post('/{token}/replies/{id}/nested-reply', [QuotationCommentPublicController::class, 'storePublicNestedReply'])->name('quotation.public.nested-reply');
    Route::put('/{token}/replies/{id}', [QuotationCommentPublicController::class, 'updatePublicReply'])->name('quotation.public.reply.update');
    Route::delete('/{token}/replies/{id}', [QuotationCommentPublicController::class, 'destroyPublicReply'])->name('quotation.public.reply.destroy');

    // ✅ Customer Approves Quotation
    Route::post('/{token}/customer-approve', [QuotationCommentController::class, 'customerApprove'])
        ->name('quotation.customer.approve');
    
    // Public export (DOC) for a quotation using token (no auth)
    Route::get('/{token}/export', [QuotationExportController::class, 'exportByToken'])->name('quotations.export.public');
});

// ---------------------------------------------------------------------------
// AUTHENTICATED ROUTES (admin | staff)
// ---------------------------------------------------------------------------

Route::middleware(['auth', 'role:admin|staff'])->group(function () {

    // 🧾 Quotation CRUD + Views
    Route::post('/add-quotation', [QuotationController::class, 'store'])->name('quotations.store');
    Route::get('/quotations/{id}', [QuotationController::class, 'show'])->whereNumber('id')->name('quotations.show');
    Route::get('/quotations/drafts', [QuotationController::class, 'drafts'])->name('quotations.drafts');
    Route::get('/quotations/approved', [QuotationController::class, 'approved'])->name('quotations.approved');
    Route::get('/quotations/rejected', [QuotationController::class, 'rejected'])->name('quotations.rejected');
    Route::get('/quotations/archive', [QuotationController::class, 'archive'])->name('quotations.archive');
    Route::get('/quotation/{id}/materials', [QuotationController::class, 'getMaterials']);
    // Admin comments fetch and submit (separated controller)
    Route::get('/quotation/{id}/comments', [QuotationCommentAdminController::class, 'getAdminComments']);
    Route::post('/quotation/{id}/comments', [QuotationCommentAdminController::class, 'storeAdminComment']);

    // 💬 Provider Replies + Approval
    Route::post('/quotations/{quotation}/reply', [QuotationController::class, 'providerReply'])->name('quotation.provider.reply');
    Route::post('/quotations/{quotation}/provider-approve', [QuotationController::class, 'providerApprove'])->name('quotation.provider.approve');

    // 💾 Export / Update / Status
    Route::get('/quotations/{id}/export', [QuotationExportController::class, 'export'])->name('quotations.export');
    Route::post('/quotations/{quotation}/update-fee', [QuotationController::class, 'updateFee'])->name('quotations.update-fee');
    Route::put('/quotations/{id}/status', [QuotationController::class, 'updateStatus'])->name('quotations.updateStatus');

    // 🧱 Drafts + Completed
    Route::get('/quotations/{id}/view-draft', [QuotationController::class, 'viewDraft'])->name('quotations.view-draft');
    Route::post('/quotations/{id}/mark-completed', [QuotationController::class, 'markCompleted'])->name('quotations.mark-completed');
    Route::get('/quotations/completed', [QuotationController::class, 'getCompleted'])->name('quotations.completed');

    // 📈 Project Progress
    Route::post('/quotations/{quotationId}/update-progress', [ProjectReportController::class, 'updateProgress'])->name('quotations.updateProgress');
    Route::get('/view-report/{id}', [ProjectReportController::class, 'showReports'])->name('quotations.showReports');

    // 🪶 Revisions
    Route::get('/quotations/{id}/revisions-json', [QuotationController::class, 'getRevisionsJson'])->name('quotations.revisions.json');
    Route::post('/quotations/{id}/create-revision', [QuotationController::class, 'createRevision'])->name('quotations.createRevision');
    
    // Clients - update client information (used inline on quotation page)
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');

});

// ---------------------------------------------------------------------------
// MATERIALS (Requires Material Management Permission)
// ---------------------------------------------------------------------------

Route::middleware(['auth', 'permission:view_materials|manage_materials'])->group(function () {
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/list', [MaterialController::class, 'list'])->name('materials.list');
    Route::post('/materials/store', [MaterialController::class, 'store'])->name('materials.store');
    Route::post('/materials/update/{id}', [MaterialController::class, 'update'])->name('materials.update');
    Route::post('/materials/{id}/update-price', [MaterialController::class, 'updatePrice']);
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
});

// ---------------------------------------------------------------------------
// QUOTATION MATERIALS (Pivot Table)
// ---------------------------------------------------------------------------

Route::post('/quotation-materials/add-selected', [QuotationMaterialController::class, 'storeSelected'])->name('quotation.materials.storeSelected');
Route::post('/quotation-materials/store', [QuotationMaterialController::class, 'store'])->name('quotation.materials.store');
Route::post('/quotation-materials/update-quantity', [QuotationMaterialController::class, 'updateQuantity'])->name('quotation.materials.updateQuantity');
Route::post('/quotation-materials/{pivotId}/update-unit-cost', [QuotationMaterialController::class, 'updateUnitCost']);
Route::delete('/quotation-materials/{pivotId}', [QuotationMaterialController::class, 'destroy'])->name('quotation.materials.destroy');

// ---------------------------------------------------------------------------
// AUTH ROUTES (Login, Register, Reset)
// ---------------------------------------------------------------------------

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login.submit')->middleware('guest');
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request')->middleware('guest');
Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('guest');
Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset')->middleware('guest');
Route::post('/reset-password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update')->middleware('guest');

// ---------------------------------------------------------------------------
// COMMENT MANAGEMENT (Public + Authenticated - auth checks inside controller)
// ---------------------------------------------------------------------------

// For authenticated users (admin/staff) - edit/delete their own comments (admin controller)
Route::middleware('auth')->group(function () {
    Route::put('/comments/{id}', [QuotationCommentAdminController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{id}', [QuotationCommentAdminController::class, 'destroy'])->name('comments.destroy');
    Route::put('/replies/{id}', [QuotationCommentAdminController::class, 'updateReply'])->name('replies.update');
    Route::delete('/replies/{id}', [QuotationCommentAdminController::class, 'destroyReply'])->name('replies.destroy');
});

// For public customers - reply to comments (no auth needed for public link) - admin side replies for authenticated use
Route::post('/comments/{id}/replies', [QuotationCommentAdminController::class, 'storeReply'])->name('comments.storeReply');
Route::post('/replies/{replyId}/nested-replies', [QuotationCommentAdminController::class, 'storeNestedReply'])->name('replies.storeNestedReply');

// ---------------------------------------------------------------------------
// DIAGNOSTIC ROUTES (for testing permissions)
// ---------------------------------------------------------------------------

Route::middleware('auth')->get('/test-permissions', function () {
    $user = \Auth::user();
    $allPerms = \Spatie\Permission\Models\Permission::all();
    $userPerms = $user->permissions->pluck('name');
    $rolePerms = $user->roles->map(function($role) {
        return $role->permissions->pluck('name');
    })->flatten()->unique();
    
    $allUserPerms = $userPerms->merge($rolePerms)->unique();
    
    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
        ],
        'roles' => $user->roles->pluck('name'),
        'direct_permissions' => $userPerms,
        'role_permissions' => $rolePerms,
        'all_permissions' => $allUserPerms,
        'total_permissions' => $allUserPerms->count(),
        'test_permissions' => [
            'view_materials' => $user->hasPermissionTo('view_materials'),
            'manage_fees' => $user->hasPermissionTo('manage_fees'),
            'view_prices' => $user->hasPermissionTo('view_prices'),
            'create_quotation' => $user->hasPermissionTo('create_quotation'),
            'manage_users' => $user->hasPermissionTo('manage_users'),
        ],
    ], 200);
});

// ---------------------------------------------------------------------------
// ADMIN ONLY ROUTES
// ---------------------------------------------------------------------------

Route::middleware(['auth'])->prefix('admin/backup')->group(function () {
    Route::get('/', [BackupManagementController::class, 'index'])->name('admin.backup.index');
    Route::post('/create', [BackupManagementController::class, 'create'])->name('admin.backup.create');
    Route::get('/download/{filename}', [BackupManagementController::class, 'download'])->name('admin.backup.download');
    Route::post('/delete', [BackupManagementController::class, 'delete'])->name('admin.backup.delete');
    Route::post('/restore', [BackupManagementController::class, 'restore'])->name('admin.backup.restore');
});


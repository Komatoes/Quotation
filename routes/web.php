<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ProjectReportController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QuotationMaterialController;
use App\Http\Controllers\QuotationExportController;
use App\Http\Controllers\QuotationCommentController;
use App\Models\Project;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login if not authenticated
Route::get('/', function () {
    return redirect()->route('login');
})->middleware('guest');

// Dashboard (requires authentication and proper role)
Route::get('/dashboard', [QuotationController::class, 'viewHome'])
    ->name('dashboard')
    ->middleware(['auth', 'role:admin|staff']);

// Reports (requires authentication and proper role)
Route::get('/view-report/{id}', [QuotationController::class, 'viewReport'])
    ->name('report')
    ->middleware(['auth', 'role:admin|staff']);



// -------------------------
// Quotations
// -------------------------

// Public routes (no auth required)
Route::prefix('quotation/public')->group(function () {
    Route::get('/{token}', [QuotationController::class, 'showPublicAccessForm'])->name('quotation.public.form');
    Route::post('/{token}/validate', [QuotationController::class, 'validatePublicAccess'])->name('quotation.public.validate');
    Route::get('/{token}/view', [QuotationController::class, 'showPublicQuotation'])->name('quotation.public.view');
    Route::post('/{token}/comment', [QuotationController::class, 'submitComment'])->name('quotation.public.comment');
    Route::post('/{token}/approve', [QuotationController::class, 'approveQuotation'])->name('quotation.public.approve');
    // Public comments fetch (no auth)
    Route::get('/{token}/comments', [QuotationController::class, 'getPublicComments'])->name('quotation.public.comments');
});

// Protected routes (require authentication and proper role)
Route::middleware(['auth', 'role:admin|staff'])->group(function () {
    // Routes that require authentication and proper role
// Secure public quotation access
Route::get('/quotation/public/{token}', [QuotationController::class, 'showPublicAccessForm'])->name('quotation.public.form');
Route::post('/quotation/public/{token}/validate', [QuotationController::class, 'validatePublicAccess'])->name('quotation.public.validate');
Route::get('/quotation/public/{token}/view', [QuotationController::class, 'showPublicQuotation'])->name('quotation.public.view');

// Quotation Comments & Approval
Route::post('/quotation/public/{token}/comment', [QuotationController::class, 'submitComment'])->name('quotation.public.comment');
Route::post('/quotation/public/{token}/approve', [QuotationController::class, 'approveQuotation'])->name('quotation.public.approve');
Route::post('/quotations/{quotation}/reply', [QuotationController::class, 'providerReply'])->name('quotation.provider.reply');
Route::post('/quotations/{quotation}/provider-approve', [QuotationController::class, 'providerApprove'])->name('quotation.provider.approve');
Route::get('/quotations/{quotation}/comments', [QuotationController::class, 'getComments'])->name('quotation.comments');

Route::post('/add-quotation', [QuotationController::class, 'store'])->name('quotations.store');

Route::get('/quotations/{id}', [QuotationController::class, 'show'])
    ->whereNumber('id')
    ->name('quotations.show');

// Export quotation to DOC
Route::get('/quotations/{id}/export', [QuotationExportController::class, 'export'])
    ->name('quotations.export');

// routes/web.php
Route::post('/quotations/{quotation}/update-fee', [QuotationController::class, 'updateFee'])
    ->name('quotations.update-fee');

Route::put('/quotations/{id}/status', [QuotationController::class, 'updateStatus'])->name('quotations.updateStatus');

// Drafts
Route::get('/quotations/drafts', [QuotationController::class, 'drafts'])->name('quotations.drafts');

// Approved
Route::get('/quotations/approved', [QuotationController::class, 'approved'])->name('quotations.approved');

// Rejected
Route::get('/quotations/rejected', [QuotationController::class, 'rejected'])->name('quotations.rejected');

Route::get('/quotation/{id}/materials', [QuotationController::class, 'getMaterials']);


Route::get('/quotations/archive', [QuotationController::class, 'archive']);



    // NEWWWWWWWWWWWWWWWWWWWWWWWW

Route::post('/quotations/{quotationId}/update-progress', [ProjectReportController::class, 'updateProgress'])
    ->name('quotations.updateProgress');

Route::get('/quotations/{id}/view-draft', [QuotationController::class, 'viewDraft'])
    ->name('quotations.view-draft');

// Route to display the progress tracking page and all past reports for a specific quotation.
Route::get('/view-report/{id}', [ProjectReportController::class, 'showReports'])->name('quotations.showReports');

// Return quotation revisions as JSON (used by the front-end modal)
Route::get('/quotations/{id}/revisions-json', [QuotationController::class, 'getRevisionsJson'])
    ->name('quotations.revisions.json');


Route::post('/quotations/{id}/mark-completed', [QuotationController::class, 'markCompleted']);
Route::post('/quotations/{id}/create-revision', [QuotationController::class, 'createRevision']);


    Route::get('/quotations/completed', [QuotationController::class, 'getCompleted']);
}); // End auth middleware group



// -------------------------
// Materials - Requires auth
// -------------------------
Route::middleware(['auth', 'permission:manage materials'])->group(function () {
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/list', [MaterialController::class, 'list'])->name('materials.list');
    Route::post('/materials/store', [MaterialController::class, 'store'])->name('materials.store');
    Route::post('/materials/update/{id}', [MaterialController::class, 'update'])->name('materials.update');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
});

// -------------------------
// Quotation Materials (pivot between quotations <-> materials)
// -------------------------
Route::post('/quotation-materials/add-selected', [QuotationMaterialController::class, 'storeSelected'])
    ->name('quotation.materials.storeSelected'); // attach existing materials
Route::post('/quotation-materials/store', [QuotationMaterialController::class, 'store'])
    ->name('quotation.materials.store'); // create + attach new material
Route::post('/quotation-materials/update-quantity', [QuotationMaterialController::class, 'updateQuantity'])
    ->name('quotation.materials.updateQuantity');
Route::delete('/quotation-materials/{pivotId}', [QuotationMaterialController::class, 'destroy'])
    ->name('quotation.materials.destroy');

// -------------------------
// Authentication Routes
// -------------------------
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])
    ->name('login')
    ->middleware('guest');

Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])
    ->name('login.submit')
    ->middleware('guest');

Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// Password Reset Routes
Route::get('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('password.request')
    ->middleware('guest');

Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email')
    ->middleware('guest');

Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])
    ->name('password.reset')
    ->middleware('guest');

Route::post('/reset-password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])
    ->name('password.update')
    ->middleware('guest');

// Admin only routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/register', [AuthenticationController::class, 'viewRegister'])->name('auth.register');
    Route::post('/create-user', [AuthenticationController::class, 'createUser'])->name('auth.createUser');
    Route::post('/quotations/{quotation}/provider-approve', [QuotationController::class, 'providerApprove'])
        ->name('quotation.provider.approve');
    Route::post('/quotations/{id}/mark-completed', [QuotationController::class, 'markCompleted']);
    Route::get('/quotations/{id}/revisions-json', [QuotationController::class, 'getRevisionsJson'])
        ->name('quotations.revisions.json');
    Route::post('/quotations/{id}/create-revision', [QuotationController::class, 'createRevision']);
    Route::put('/quotations/{id}/status', [QuotationController::class, 'updateStatus'])
        ->name('quotations.updateStatus');
});

// Comments routes
Route::post('/quotation/public/{token}/comment', [QuotationCommentController::class, 'storePublicComment'])->name('quotation.comment.submit');
Route::get('/quotation/public/{token}/comments', [QuotationCommentController::class, 'getComments'])->name('quotation.public.comments');
Route::post('/quotation/{id}/admin/comment', [QuotationCommentController::class, 'storeAdminComment'])->name('quotation.admin.comment');
Route::post('/quotation/comment/{id}/reply', [QuotationCommentController::class, 'adminReply'])->name('quotation.admin.reply');
Route::post('/quotation/{id}/approve', [QuotationCommentController::class, 'approveQuotation'])->name('quotation.approve');
Route::post('/quotation/public/{token}/customer-approve', [QuotationCommentController::class, 'customerApprove'])->name('quotation.customer.approve');



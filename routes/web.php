<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ProjectReportController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QuotationMaterialController;
use App\Http\Controllers\QuotationExportController;
use App\Models\Project;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// -------------------------
// Home / Dashboard
// -------------------------
Route::get('/', [QuotationController::class, 'viewHome'])->name('dashboard');

// web.php
Route::get('/view-report/{id}', [QuotationController::class, 'viewReport'])->name('report');



// -------------------------
// Quotations
// -------------------------
// Public route for guest/client to view quotation or report
Route::get('/quotation/public/{token}', [QuotationController::class, 'publicView'])->name('quotation.public');
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


Route::post('/quotations/{id}/mark-completed', [QuotationController::class, 'markCompleted']);
Route::post('/quotations/{id}/create-revision', [QuotationController::class, 'createRevision']);


Route::get('/quotations/completed', [QuotationController::class, 'getCompleted']);



// -------------------------
// Materials
// -------------------------
Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
Route::get('/materials/list', [MaterialController::class, 'list'])->name('materials.list');
Route::post('/materials/store', [MaterialController::class, 'store'])->name('materials.store');
Route::post('/materials/update/{id}', [MaterialController::class, 'update'])->name('materials.update');
Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

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
// Authentication (commented for now, but left for future use)
// -------------------------
// Route::get('/login', [AuthenticationController::class, 'viewLogin'])->name('auth.login');
// Route::post('/login-user', [AuthenticationController::class, 'loginUser'])->name('auth.loginUser');
// Route::get('/logout-user', [AuthenticationController::class, 'logoutUser'])->name('auth.logout');
// Route::get('/register', [AuthenticationController::class, 'viewRegister'])->name('auth.register');
// Route::post('/create-user', [AuthenticationController::class, 'createUser'])->name('auth.createUser');

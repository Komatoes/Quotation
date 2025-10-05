<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QuotationMaterialController;
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
Route::post('/add-quotation', [QuotationController::class, 'store'])->name('quotations.store');

Route::get('/quotations/{id}', [QuotationController::class, 'show'])
    ->whereNumber('id')
    ->name('quotations.show');

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

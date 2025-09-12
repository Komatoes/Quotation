<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\QuotationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaterialController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [QuotationController::class, 'viewHome']);

Route::get('/quotations/{id}', [QuotationController::class, 'show'])->name('quotations.show');

Route::get('/login', [AuthenticationController::class, 'viewLogin']);

Route::post('/login-user', [AuthenticationController::class, 'loginUser']);

Route::get('/logout-user', [AuthenticationController::class, 'logoutUser']);

Route::get('/register', [AuthenticationController::class, 'viewRegister']);

Route::post('/create-user', [AuthenticationController::class, 'createUser']);

Route::post('/add-quotation', [QuotationController::class, 'store']);

Route::post('/add-materialquotation', [QuotationController::class, 'addMaterials']);

Route::post('/add-material', [MaterialController::class, 'store'])->name('materials.store');

Route::delete('/quotation-materials/{pivotId}', [QuotationController::class, 'destroy'])->name('quotation-materials.destroy');

Route::post('/quotation-materials/update-quantity', [QuotationController::class, 'updateQuantity'])->name('quotation-materials.updateQuantity');

Route::put('/quotations/{id}/status', [QuotationController::class, 'updateStatus'])->name('quotations.updateStatus');

Route::post('/edit-material', [MaterialController::class, 'update'])->name('materials.update');

Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');

Route::get('/materials/list', [MaterialController::class, 'list'])->name('materials.list');

Route::post('/materials/update/{id}', [MaterialController::class, 'update'])->name('materials.update');

Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

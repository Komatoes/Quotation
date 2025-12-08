<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuotationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Quotation Management Routes
Route::middleware('auth:sanctum')->group(function () {
    // Reject a quotation with required reason
    Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'reject'])
        ->name('quotations.reject');
    
    // Create a linked/add-on quotation
    Route::post('/quotations/{parentQuotationId}/linked', [QuotationController::class, 'createLinkedQuotation'])
        ->name('quotations.createLinked');
    
    // Get all linked quotations for a quotation
    Route::get('/quotations/{quotation}/linked', [QuotationController::class, 'getLinkedQuotations'])
        ->name('quotations.getLinked');
});

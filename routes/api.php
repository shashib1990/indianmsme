<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('locations')->name('api.locations.')->group(function () {
    Route::get('/states', [\App\Http\Controllers\Api\LocationApiController::class, 'states'])->name('states');
    Route::get('/districts', [\App\Http\Controllers\Api\LocationApiController::class, 'districts'])->name('districts');
    Route::get('/pincodes', [\App\Http\Controllers\Api\LocationApiController::class, 'pincodes'])->name('pincodes');
});

// Public Unauthenticated API: Update Abhhyam Code by ADC Code (POST /api/adc/update-abhhyam-code)
Route::post('/adc/update-abhhyam-code', [\App\Http\Controllers\Api\AdcApiController::class, 'updateAbhhyamCode'])->name('api.adc.update-abhhyam-code');

// POST API: Fetch complete ADC data with canteen name, stock summary, and product catalog (POST /api/adc/details)
Route::post('/adc/details', [\App\Http\Controllers\Api\AdcApiController::class, 'getAdcDetails'])->name('api.adc.details');

// Public Unauthenticated API: Update Customer Abhhyam Code by Phone/Mobile (POST /api/pos-customer/update-abhhyam-code or /api/adc-customer/update-abhhyam-code or /api/ddc-customer/update-abhhyam-code)
Route::post('/pos-customer/update-abhhyam-code', [\App\Http\Controllers\Api\AdcApiController::class, 'updateCustomerAbhhyamCode'])->name('api.pos-customer.update-abhhyam-code');
Route::post('/adc-customer/update-abhhyam-code', [\App\Http\Controllers\Api\AdcApiController::class, 'updateCustomerAbhhyamCode'])->name('api.adc-customer.update-abhhyam-code');
Route::post('/ddc-customer/update-abhhyam-code', [\App\Http\Controllers\Api\AdcApiController::class, 'updateDdcCustomerAbhhyamCode'])->name('api.ddc-customer.update-abhhyam-code');


Route::get('/adc', [\App\Http\Controllers\Api\DataApiController::class, 'activeAdc'])->name('api.adc');
Route::get('/ddc', [\App\Http\Controllers\Api\DataApiController::class, 'Ddc'])->name('api.ddc');
Route::get('/seller', [\App\Http\Controllers\Api\DataApiController::class, 'seller'])->name('api.seller');
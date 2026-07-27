<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VendController;
use App\Http\Controllers\Api\BundleController;
use App\Http\Controllers\Api\TvController;
use App\Http\Controllers\Api\ElectricityController;
use App\Http\Controllers\Api\BettingController;


Route::middleware('client.auth')->group(function () {

    Route::post('/vend', [VendController::class, 'vend']);

    Route::post('/requery', [VendController::class, 'requery']);

    Route::get('/bundles', [BundleController::class, 'index']);

    Route::post('/tv/validate', [TvController::class, 'validateCustomer']);

    Route::post('/tv/subscription-status', [TvController::class, 'subscriptionStatus']);

    Route::post('/tv/addons', [TvController::class, 'addons']);

    Route::post('/electricity/validate', [ElectricityController::class, 'validateElectricity']);

    Route::post('/betting/validate', [BettingController::class, 'validateBetting']);

});

// Compatibility endpoint
Route::post('/b2b', [VendController::class, 'oatek']);

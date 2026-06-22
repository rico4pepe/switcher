<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VendController;
use App\Http\Controllers\Api\BundleController;
use App\Http\Controllers\Api\TvController;


Route::post('/vend', [VendController::class, 'vend']);
//Route::post('/b2b', [VendController::class, 'oatek']);

Route::post('/b2b', [VendController::class, 'oatek']);

Route::post('/requery/{transaction}', [VendController::class, 'requery']);
Route::get(
    '/bundles',
    [BundleController::class, 'index']
);

Route::post(
    '/tv/validate',
    [TvController::class, 'validateCustomer']
);

Route::post(
    '/tv/subscription-status',
    [TvController::class, 'subscriptionStatus']
);

Route::post(
    '/tv/addons',
    [TvController::class, 'addons']
);

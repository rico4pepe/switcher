<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VendController;

Route::post('/vend', [VendController::class, 'vend']);
//Route::post('/b2b', [VendController::class, 'oatek']);

Route::post('/b2b', [VendController::class, 'oatek']);

Route::post('/requery/{transaction}', [VendController::class, 'requery']);

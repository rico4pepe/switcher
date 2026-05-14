<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Operations\TransactionController;
use App\Http\Controllers\Operations\VendorHealthController;
use App\Http\Controllers\Operations\RoutingControlController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])
    ->prefix('operations')
    ->name('operations.')
    ->group(function () {

      Route::get('/transactions', [TransactionController::class, 'index'])
    ->name('transactions.index');

    Route::get('/transactions/{transaction}', [
    TransactionController::class,
    'show',
])->name('transactions.show');

Route::post('/transactions/{transaction}/requery', [
    TransactionController::class,
    'requery',
])->name('transactions.requery');


Route::get('/vendors', [
    VendorHealthController::class,
    'index',
])->name('vendors.index');

Route::get('/vendors/create', [
    VendorHealthController::class,
    'create',
])->name('vendors.create');

Route::post('/vendors', [
    VendorHealthController::class,
    'store',
])->name('vendors.store');

Route::get('/vendors/{vendor}', [
    VendorHealthController::class,
    'show',
])->name('vendors.show');

Route::post('/vendors/{vendor}/toggle', [
    VendorHealthController::class,
    'toggle',
])->name('vendors.toggle');

Route::get('/routing', [
    RoutingControlController::class,
    'index',
])->name('routing.index');

Route::post('/routing/{routingConfig}/toggle-mode', [
    RoutingControlController::class,
    'toggleMode',
])->name('routing.toggle-mode');

Route::get('/routing/{routingConfig}', [
    RoutingControlController::class,
    'show',
])->name('routing.show');

Route::put('/routing/{routingConfig}', [
    RoutingControlController::class,
    'update',
])->name('routing.update');


});



require __DIR__.'/auth.php';

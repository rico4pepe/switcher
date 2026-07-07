<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Operations\TransactionController;
use App\Http\Controllers\Operations\VendorHealthController;
use App\Http\Controllers\Operations\RoutingControlController;
use App\Http\Controllers\Operations\ClientController;
use App\Http\Controllers\Operations\ClientRoutingController;

Route::redirect('/', '/login');

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

//  Route::get(
//             '/clients',
//             [ClientController::class, 'index']
//         )->name('clients.index');


//              Route::get(
//             '/clients/{client}',
//             [ClientController::class, 'show']
//         )->name('clients.show');


        Route::resource('clients', ClientController::class)
    ->except(['destroy']);

    Route::post('/clients/{client}/toggle', [
    ClientController::class,
    'toggle',
])->name('clients.toggle');

Route::post(
    '/clients/{client}/regenerate-key',
    [ClientController::class, 'regenerateKey']
)->name('clients.regenerate-key');


Route::get(
    '/client-routing/{clientRoutingConfig}/edit',
    [ClientRoutingController::class, 'edit']
)->name('client-routing.edit');

Route::put(
    '/client-routing/{clientRoutingConfig}',
    [ClientRoutingController::class, 'update']
)->name('client-routing.update');


Route::get(
    '/clients/{client}/routing/create',
    [ClientRoutingController::class, 'create']
)->name('client-routing.create');

Route::post(
    '/clients/{client}/routing',
    [ClientRoutingController::class, 'store']
)->name('client-routing.store');

Route::get(
    '/transactions/export/csv',
    [
        TransactionController::class,
        'exportCsv',
    ]
)->name('transactions.export.csv');

});



require __DIR__.'/auth.php';

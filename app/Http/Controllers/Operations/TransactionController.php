<?php

namespace App\Http\Controllers\Operations;

use Illuminate\View\View;
use App\Filters\TransactionFilter;
use App\Http\Controllers\Controller;
use App\Actions\Transactions\GetTransactionsAction;
use App\Models\Transaction;
use App\Services\VendService;
use Illuminate\Http\RedirectResponse;
use App\Models\Vendor;


class TransactionController extends Controller
{
    public function index(
        GetTransactionsAction $getTransactionsAction,
        TransactionFilter $filter
    ): View {

        $vendors = Vendor::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

       $data = $getTransactionsAction->execute($filter);

       return view('operations.transactions.index', [

    'transactions' => $data['transactions'],

    'metrics' => $data['metrics'],
     'vendors' => $vendors,
]);
    }


    public function show(
    Transaction $transaction
): View {

    $transaction->load([
        'vendor',
        'client',
       'events' => fn ($query) => $query->latest(),
    ]);

    return view('operations.transactions.show', [
        'transaction' => $transaction,
    ]);
}

public function requery(
    Transaction $transaction,
    VendService $vendorService
): RedirectResponse {

    $vendorService->requery($transaction);

    return redirect()
        ->route('operations.transactions.show', $transaction)
        ->with('success', 'Transaction requery initiated.');
}


}

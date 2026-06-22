<?php

namespace App\Actions\Transactions;

use App\Models\Transaction;
use App\Filters\TransactionFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetTransactionsAction
{
  public function execute(
    TransactionFilter $filter
): array {

    $query = Transaction::query()
        ->with([
            'vendor',
            'client',
            'events' => function ($query) {

                $query->latest();
            },
        ])
        ->latest();

    $transactions = $filter
        ->apply($query)
        ->paginate(10)
        ->withQueryString();

    $metrics = [

        'totalToday' => Transaction::whereDate(
            'created_at',
            today()
        )->count(),

        'successfulToday' => Transaction::whereDate(
            'created_at',
            today()
        )->where('status', 'success')->count(),

        'failedToday' => Transaction::whereDate(
            'created_at',
            today()
        )->where('status', 'failed')->count(),

        'pendingToday' => Transaction::whereDate(
            'created_at',
            today()
        )->where('status', 'pending')->count(),
    ];

    return [

        'transactions' => $transactions,

        'metrics' => $metrics,
    ];
}
}

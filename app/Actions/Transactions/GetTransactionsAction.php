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

    $successRate = $metrics['totalToday'] > 0
    ? round(
        ($metrics['successfulToday'] / $metrics['totalToday']) * 100,
        2
    )
    : 0;

$avgLatency = Transaction::whereDate(
    'created_at',
    today()
)
->where('status', 'success')
->avg('response_time_ms');

$metrics['successRate'] = $successRate;

$metrics['avgLatency'] = round(
    ($avgLatency ?? 0) / 1000,
    2
);

    return [

        'transactions' => $transactions,

        'metrics' => $metrics,


    ];
}
}

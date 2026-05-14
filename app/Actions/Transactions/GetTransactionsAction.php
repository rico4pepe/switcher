<?php

namespace App\Actions\Transactions;

use App\Models\Transaction;
use App\Filters\TransactionFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetTransactionsAction
{
    public function execute(
        TransactionFilter $filter
    ): LengthAwarePaginator {

        $query = Transaction::query()
            ->with([
                'vendor',
                'client',
            ])
            ->latest();

        return $filter
            ->apply($query)
            ->paginate(10)
            ->withQueryString();
    }
}

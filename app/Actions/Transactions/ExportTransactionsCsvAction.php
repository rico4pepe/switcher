<?php

namespace App\Actions\Transactions;

use App\Filters\TransactionFilter;
use App\Models\Client;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportTransactionsCsvAction
{
    public function execute(
        TransactionFilter $filter
    ): StreamedResponse
    {
        $query = Transaction::query()
            ->with([
                'vendor',
                'client',
            ])
            ->latest();

        $transactions = $filter
            ->apply($query)
            ->get();

        //$filename = 'transactions.csv';
        $client = null;

        if (request()->filled('client')) {

    $client = Client::find(request('client'));

}

$prefix = $client
    ? \Illuminate\Support\Str::slug($client->organization_name)
    : 'transactions';

$from = request('from') ?? today()->toDateString();

$to = request('to') ?? today()->toDateString();

$filename = "{$prefix}_transactions_{$from}_to_{$to}.csv";

return response()->streamDownload(
    function () use ($transactions) {

        $handle = fopen('php://output', 'w');

        fputcsv($handle, [
            'Tracking ID',
            'Client',
            'Customer',
            'Product',
            'Amount',
            'Vendor',
            'Status',
            'Created At',
        ]);


        foreach ($transactions as $transaction) {

    fputcsv($handle, [

        $transaction->tracking_id,

        $transaction->client?->organization_name,

        $transaction->beneficiary,

        ucfirst($transaction->product_type),

        $transaction->amount,

        $transaction->vendor?->name,

        strtoupper($transaction->status),

        $transaction->created_at->format('Y-m-d H:i:s'),

    ]);

}

        fclose($handle);

    },
    $filename
);
    }
}

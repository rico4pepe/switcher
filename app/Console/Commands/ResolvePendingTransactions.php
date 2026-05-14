<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Services\VendService;

class ResolvePendingTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:resolve-pending';

    /**
     * The console command description.
     *
     * @var string
     */
   protected $description = 'Requery pending transactions';

    /**
     * Execute the console command.
     */
    public function handle(VendService $service): void
    {
        Transaction::query()
            ->where('status', 'pending')
            ->whereNotNull('vendor_id')
            ->orderBy('id')
            ->chunkById(100, function ($transactions) use ($service) {

                foreach ($transactions as $transaction) {

                    $this->info(
                        "Requerying transaction {$transaction->id}"
                    );

                    try {

                        $service->requery($transaction);

                    } catch (\Throwable $e) {

                        report($e);

                        $this->error(
                            "Failed transaction {$transaction->id}: {$e->getMessage()}"
                        );
                    }
                }
            });
    }
}

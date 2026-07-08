<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vendor;
use App\Services\ProductSynchronizationService;

class SyncProductsCommand extends Command
{

    public function __construct(
    protected ProductSynchronizationService $syncService
) {
    parent::__construct();
}
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
protected $signature = 'switcher:sync-products
                        {driver : Driver key (e.g. vendify, oatek)}
                        {network : Network (e.g. MTN)}';

    /**
     * The console command description.
     *
     * @var string
     */
   protected $description = 'Synchronize vendor products into the Switcher product catalog.';

    /**
     * Execute the console command.
     */
public function handle(): int
{
    $driverKey = strtolower($this->argument('driver'));
    $network = strtoupper($this->argument('network'));

    $vendor = Vendor::where(
        'driver_key',
        $driverKey
    )->first();

    if (! $vendor) {
        $this->error("Driver '{$driverKey}' not found.");

        return self::FAILURE;
    }

    $this->info("Synchronizing {$vendor->name} ({$network})...");

    $this->syncService->synchronize(
        $vendor,
        'data',
        $network
    );

    $this->info('Synchronization completed successfully.');

    return self::SUCCESS;
}
}

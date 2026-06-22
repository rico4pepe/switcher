<?php

namespace App\Services\Vendors;

use RuntimeException;

class VendorDriverRegistry
{
    public static function drivers(): array
    {
        return [

            'mock' => [
                'label' => 'Mock Driver',
                'class' => MockVendorDriver::class,
            ],

            'oatek' => [
                'label' => 'Oatek Driver',
                'class' => OatekDriver::class,
            ],

            'vendify' => [
                'label' => 'Vendify Driver',
                'class' => VendifyDriver::class,
            ],

        ];
    }

    public function resolve(string $driverKey): VendorInterface
    {
        $drivers = self::drivers();

        if (! isset($drivers[$driverKey])) {

            throw new RuntimeException(
                "Vendor driver [{$driverKey}] not registered."
            );
        }

        $driverClass = $drivers[$driverKey]['class'];

        return app($driverClass);
    }
}

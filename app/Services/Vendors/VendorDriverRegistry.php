<?php

namespace App\Services\Vendors;

use RuntimeException;

class VendorDriverRegistry
{
   public static function drivers(): array
    {
        return [

            'mock' => [
                'class' => MockVendorDriver::class,
            ],

            'oatek' => [
                'class' => OatekDriver::class,
            ],

            'vendify' => [
                'class' => VendifyDriver::class,
            ],

        ];
    }
 public static function has(string $driverKey): bool
    {
        return isset(self::drivers()[$driverKey]);
    }

    public static function class(string $driverKey): string
    {
        if (! self::has($driverKey)) {

            throw new RuntimeException(
                "Vendor driver [{$driverKey}] not registered."
            );
        }

        return self::drivers()[$driverKey]['class'];
    }
}

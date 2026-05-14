<?php

namespace App\Services\Vendors;

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
}

<?php

namespace App\Services;

use App\Models\RoutingConfig;
use App\Services\Vendors\VendorDriverResolver;

class ElectricityValidationService
{
    public function __construct(
        protected VendorDriverResolver $resolver
    ) {
    }

    public function validate(
        string $meterNo,
        string $disco,
        string $type
    ): array {

        $routing = RoutingConfig::where(
            'product_type',
            'electricity'
        )
        ->where(
            'network',
            strtoupper($disco)
        )
        ->where(
            'is_active',
            true
        )
        ->firstOrFail();

        $driver = $this->resolver->resolve(
            $routing->primary_vendor_id
        );

        return $driver->validateElectricity(
            $meterNo,
            $disco,
            $type
        );
    }
}

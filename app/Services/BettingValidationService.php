<?php

namespace App\Services;

use App\Models\RoutingConfig;
use App\Services\Vendors\VendorDriverResolver;

class BettingValidationService
{
    public function __construct(
        protected VendorDriverResolver $resolver
    ) {
    }

    public function validate(
        string $customerId,
        string $biller
    ): array {

        $routing = RoutingConfig::where(
            'product_type',
            'betting'
        )
        ->where(
            'network',
            strtoupper($biller)
        )
        ->where(
            'is_active',
            true
        )
        ->firstOrFail();

        $driver = $this->resolver->resolve(
            $routing->primary_vendor_id
        );

        return $driver->validateBetting(
            $customerId,
            $biller
        );
    }
}

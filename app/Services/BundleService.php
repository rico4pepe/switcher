<?php

namespace App\Services;

use App\Models\Vendor;
use App\Services\Vendors\VendorDriverResolver;
use App\Models\RoutingConfig;

class BundleService
{
    public function __construct(
        protected VendorDriverResolver $resolver
    ) {
    }

public function fetch(
    string $network
): array {

    $route = RoutingConfig::query()

        ->where(
            'product_type',
            'data'
        )

        ->where(
            'network',
            strtoupper($network)
        )

        ->where(
            'is_active',
            true
        )

        ->firstOrFail();

    $driver = $this->resolver->resolve(
        $route->primary_vendor_id
    );

    return $driver->fetchBundles(
        $network
    );
}
}

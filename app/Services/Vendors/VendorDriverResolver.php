<?php

namespace App\Services\Vendors;

use App\Models\Vendor;
use Illuminate\Contracts\Container\Container;

class VendorDriverResolver
{
    public function __construct(
        protected Container $container
    ) {
    }

 public function resolve(
    int $vendorId
): VendorInterface {

    $vendor = Vendor::findOrFail($vendorId);

    $driverClass = VendorDriverRegistry::class(
        $vendor->driver_key
    );

    return $this->container->make(
        $driverClass
    );
}
}

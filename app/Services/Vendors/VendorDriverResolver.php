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

        $drivers = VendorDriverRegistry::drivers();

        if (! isset($drivers[$vendor->driver_key])) {

            throw new \Exception('Unsupported driver');
        }

        $driverClass = $drivers[$vendor->driver_key]['class'];

        return $this->container->make(
            $driverClass
        );
    }
}

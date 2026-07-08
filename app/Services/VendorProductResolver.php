<?php

namespace App\Services;

use App\Models\Vendor;
use Illuminate\Validation\ValidationException;

class VendorProductResolver
{
    public function __construct(
        protected ProductCatalogService $catalog
    ) {
    }

    /**
     * Resolve a Switcher product code into a
     * vendor-specific product code.
     */
    public function resolve(
        string $driverKey,
        ?string $productCode
    ): string {

        if (empty($productCode)) {

            throw ValidationException::withMessages([
                'product_code' => [
                    'Product code is required.'
                ]
            ]);
        }

        $vendor = Vendor::query()

            ->where(
                'driver_key',
                $driverKey
            )

            ->first();

        if (! $vendor) {

            throw ValidationException::withMessages([
                'driver' => [
                    "Vendor driver '{$driverKey}' not found."
                ]
            ]);
        }

        $vendorProductCode = $this->catalog
            ->resolveVendorCode(
                $productCode,
                $driverKey
            );

        if (! $vendorProductCode) {

            throw ValidationException::withMessages([
                'product_code' => [
                    "No vendor mapping found for '{$productCode}'."
                ]
            ]);
        }

        return $vendorProductCode;
    }
}

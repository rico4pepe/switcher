<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Vendor;
use App\Models\VendorProductMapping;
use App\Services\Vendors\VendorDriverResolver;
use Illuminate\Support\Facades\DB;

class ProductSynchronizationService
{
    private const UNKNOWN_NETWORK = 'NA';
private const UNKNOWN_VALIDITY = 'NA';
    public function __construct(
        protected VendorDriverResolver $resolver
    ) {
    }

    /**
     * Synchronize products for a vendor.
     */
    public function synchronize(
        Vendor $vendor,
         string $productType,
        ?string $network = null
    ): void {

        $driver = $this->resolver->resolve(
            $vendor->id
        );

        $bundles = $driver->fetchBundles(
            $network
        );


        DB::transaction(function () use (
            $vendor,
            $productType,
            $bundles
        ) {

            foreach ($bundles as $bundle) {

                $this->syncBundle(
                    $vendor,
                    $productType,
                    $bundle
                );
            }

        });
    }

    /**
     * Synchronize one bundle.
     */protected function syncBundle(
    Vendor $vendor,
    string $productType,
    array $bundle
): void
{
    $product = $this->createOrUpdateProduct(
        $productType,
        $bundle
    );

    $this->createOrUpdateMapping(
        $vendor,
        $product,
        $bundle
    );
}






protected function generateProductCode(
    string $productType,
    array $bundle
): string
{
    return sprintf(
        '%s_%s_%s_%s',
        strtoupper($productType),
        strtoupper($bundle['network'] ?? self::UNKNOWN_NETWORK),
        strtoupper($bundle['allowance'] ?? 'UNKNOWN'),
        $bundle['validity'] ?? self::UNKNOWN_VALIDITY
    );
}


protected function createOrUpdateProduct(
    string $productType,
    array $bundle
): Product
{
    return Product::updateOrCreate(
        [
            'product_code' => $this->generateProductCode(
                $productType,
                $bundle
            ),
        ],
        [
            'product_type' => strtolower($productType),
            'network' => $bundle['network'] ?? null,

            'display_name' => $bundle['display_name'] ?? '',

            'allowance' => $bundle['allowance'] ?? null,

            'description' => $bundle['display_name'] ?? '',

            'amount' => $bundle['amount'] ?? 0,

            'validity' => $bundle['validity'] ?? null,

            'category' => $bundle['category'] ?? null,

            'is_active' => true,

            'metadata' => [],
        ]
    );
}

protected function createOrUpdateMapping(
    Vendor $vendor,
    Product $product,
    array $bundle
): ?VendorProductMapping
{
    $vendorProductCode = $bundle['vendor_product_code']
        ?? $bundle['product_id']
        ?? null;

    if (empty($vendorProductCode)) {

        logger()->warning(
            'Skipping vendor product without vendor product code.',
            [
                'vendor' => $vendor->name,
                'product_code' => $product->product_code,
                'bundle' => $bundle,
            ]
        );

        return null;
    }

    return VendorProductMapping::updateOrCreate(
        [
            'vendor_id' => $vendor->id,
            'product_id' => $product->id,
        ],
        [
            'vendor_product_code' => $vendorProductCode,

            'vendor_product_name' => $bundle['display_name'],

            'vendor_metadata' => $bundle,

            'is_active' => true,
        ]
    );
}

}

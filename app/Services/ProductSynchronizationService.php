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


    protected function normalizeAllowance(
    string $allowance
): string
{
    $allowance = strtoupper(trim($allowance));

    // Remove all spaces
    $allowance = str_replace(' ', '', $allowance);

    // Convert common MB values to GB
    return match ($allowance) {
        '1024MB', '1000MB' => '1GB',
        '2048MB', '2000MB' => '2GB',
        '3072MB', '3000MB' => '3GB',
        '5120MB', '5000MB' => '5GB',
        '10240MB', '10000MB' => '10GB',
        default => $allowance,
    };
}

protected function normalizeValidity(
    mixed $validity
): string
{
    if (empty($validity)) {
          return self::UNKNOWN_VALIDITY;

    }

    if (is_numeric($validity)) {
        return (int) $validity . 'D';
    }

    $validity = strtoupper(trim($validity));

    // Remove common words
    $validity = str_replace(
        ['DAYS', 'DAY', ' '],
        '',
        $validity
    );

    if (is_numeric($validity)) {
        return $validity . 'D';
    }

    return $validity;
}

protected function generateProductCode(
    string $productType,
    array $bundle
): string
{
    $network = strtoupper(
         $bundle['network'] ?? self::UNKNOWN_NETWORK
    );

    $productType = strtoupper($productType);

    $allowance = $this->normalizeAllowance(
        $bundle['allowance'] ?? ''
    );

    $validity = $this->normalizeValidity(
        $bundle['validity'] ?? null
    );

    return sprintf(
        '%s_%s_%s_%s',
        $productType,
        $network,
        $allowance,
        $validity
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

            'display_name' => $this->normalizeAllowance(
    $bundle['allowance'] ?? ''
),

            'description' => $this->normalizeAllowance(
    $bundle['allowance'] ?? ''
),

            'amount' => $bundle['amount'] ?? 0,

           'validity' => (int) filter_var(
    $bundle['validity'] ?? 0,
    FILTER_SANITIZE_NUMBER_INT
),

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

            'vendor_product_name' => $this->normalizeAllowance(
                $bundle['allowance'] ?? ''
            ),

            'vendor_metadata' => $bundle,

            'is_active' => true,
        ]
    );
}

}

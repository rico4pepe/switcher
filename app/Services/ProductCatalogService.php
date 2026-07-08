<?php

namespace App\Services;

use App\Models\Product;
use App\Models\VendorProductMapping;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Vendor;

class ProductCatalogService
{
    /**
     * Get all active products.
     */
    public function all(): Collection
    {
        return Product::active()
            ->orderBy('sort_order')
            ->orderBy('display_name')
            ->get();
    }

    /**
     * Find product by product code.
     */
    public function findByCode(
        string $productCode
    ): ?Product {
        return Product::active()

            ->where(
                'product_code',
                $productCode
            )

            ->first();
    }

    /**
     * Get products by type.
     */
    public function byType(
        string $productType
    ): Collection {

        return Product::active()

            ->productType(
                $productType
            )

            ->orderBy('sort_order')

            ->get();
    }

    /**
     * Get products by type and network.
     */
    public function byNetwork(
        string $productType,
        string $network
    ): Collection {

        return Product::active()

            ->productType(
                $productType
            )

            ->network(
                $network
            )

            ->orderBy('sort_order')

            ->get();
    }

    /**
     * Resolve vendor mapping.
     */
public function resolveVendorProduct(
    Product $product,
    int $vendorId
): ?VendorProductMapping
{
    return VendorProductMapping::active()
        ->vendor($vendorId)
        ->product($product->id)
        ->first();
}

    public function find(
    int $id
): ?Product
{
    return Product::active()->find($id);
}

public function resolveVendorCode(
    string $productCode,
    string $driverKey
): ?string
{
    $product = $this->findByCode($productCode);

    if (! $product) {
        return null;
    }

    $vendor = Vendor::where(
        'driver_key',
        $driverKey
    )->first();

    if (! $vendor) {
        return null;
    }

    return $this->resolveVendorProduct(
        $product,
        $vendor->id
    )?->vendor_product_code;
}


}

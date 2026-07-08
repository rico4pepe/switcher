<?php

namespace App\Services;

class BundleService
{
    public function __construct(
        protected ProductCatalogService $catalog
    ) {
    }

 public function fetch(
    string $network
): array {

    return $this->catalog
        ->byNetwork(
            'data',
            $network
        )
        ->map(function ($product) {

            return [

                'product_code' => $product->product_code,

                'display_name' => $product->display_name,

                'amount' => $product->amount,

                'validity' => $product->validity,

                'category' => $product->category,
            ];

        })

        ->values()

        ->toArray();
}
}

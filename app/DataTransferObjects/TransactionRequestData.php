<?php

namespace App\DataTransferObjects;

class TransactionRequestData
{
    public function __construct(
        public readonly string $tracking_id,
        public readonly string $client_id,
        public readonly string $product_type,

        public readonly ?string $network = null,

        public readonly ?string $beneficiary = null,

        public readonly ?float $amount = null,

        // New canonical Switcher product identifier
        public readonly ?string $product_code = null,

        // Temporary - retained for backward compatibility.
        // Will be removed after all vendor drivers migrate
        // to ProductCatalogService.
        public readonly ?string $product_id = null,

        public readonly ?string $package_code = null,

        public readonly ?string $package_name = null,

        public readonly ?int $period = null,

        public readonly ?bool $has_addon = null,

        public readonly ?string $addon_code = null,

        public readonly ?string $addon_name = null,

        public readonly ?string $meter_type = null,

        public readonly ?string $phone_number = null,

        public readonly ?string $customer_name = null,

        public readonly array $meta = [],
    ) {}
}

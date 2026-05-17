<?php

namespace App\DataTransferObjects;

class TransactionRequestData
{
    public function __construct(
        public readonly string $tracking_id,
        public readonly string $client_id,
        public readonly string $product_type,
        public readonly string $network,
        public readonly ?string $beneficiary,
        public readonly float $amount,
        public readonly array $meta = [],
    ) {}
}

<?php

namespace App\Services\Vendors;
use App\Models\Transaction;
class VendifyDriver extends BaseVendorDriver implements VendorInterface
{
     public function vend(array $payload): array
    {
       return $this->success(
            message: 'Vendify transaction successful',
            vendorReference: 'VEND-' . rand(1000, 9999),
            raw: [
                'vendor' => 'vendify',
                'payload' => $payload,
            ]
        );
    }

    public function requery(
        Transaction $transaction
    ): array {

        throw new \Exception('Oatek requery not implemented yet');
    }
}

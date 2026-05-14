<?php

namespace App\Services\Vendors;
use App\Models\Transaction;
class ZeedlabDriver extends BaseVendorDriver implements VendorInterface
{
     public function vend(array $payload): array
    {
        throw new \Exception('OatekDriver not implemented yet');
    }

    public function requery(
        Transaction $transaction
    ): array {

        throw new \Exception('Oatek requery not implemented yet');
    }
}

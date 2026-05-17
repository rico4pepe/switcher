<?php

namespace App\Services\Vendors;

use App\Models\Transaction;
use App\DataTransferObjects\TransactionRequestData;

interface VendorInterface
{
          public function vend(
    TransactionRequestData $payload
): array;
           public function requery(Transaction $transaction): array;
}

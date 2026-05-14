<?php

namespace App\Services\Vendors;

use App\Models\Transaction;

interface VendorInterface
{
          public function vend(array $payload): array;
           public function requery(Transaction $transaction): array;
}

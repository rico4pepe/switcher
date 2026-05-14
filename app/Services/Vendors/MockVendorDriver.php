<?php

namespace App\Services\Vendors;

use App\Models\Transaction;
 class MockVendorDriver extends BaseVendorDriver implements VendorInterface
{


   public function vend(array $payload): array
{
    $mode = request()->header('X-MOCK-MODE', 'success');

    return match ($mode) {
        'fail' => [
            'status' => 'failed',
            'code' => '99',
            'message' => 'Vendor failure'
        ],

        'pending' => [
            'status' => 'pending',
            'code' => '02',
            'message' => 'Processing'
        ],

        'timeout' => $this->simulateTimeout(),

        'success' => [
            'status' => 'success',
            'code' => '00',
            'vendor_reference' => uniqid('VND_'),
            'message' => 'Success'
        ],

        default => [
            'status' => 'failed',
            'code' => '98',
            'message' => 'Unknown mock mode'
        ],
    };
}

private function simulateTimeout(): array
{
    // simulate no response / network failure
    throw new \Exception('Connection timeout');
}

public function requery(Transaction $transaction): array
{
      return [
        'status' => 'success',
        'code' => '00',
        'vendor_reference' => $transaction->vendor_reference,
        'message' => 'Requery successful',
    ];
}

}

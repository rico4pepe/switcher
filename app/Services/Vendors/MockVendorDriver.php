<?php

namespace App\Services\Vendors;
use App\DataTransferObjects\TransactionRequestData;
use App\Data\Responses\NormalizedVendorResponse;

use App\Models\Transaction;
 class MockVendorDriver extends BaseVendorDriver implements VendorInterface
{


  public function vend(
    TransactionRequestData $payload
): NormalizedVendorResponse
{
    $mode = request()->header('X-MOCK-MODE', 'success');

  return new NormalizedVendorResponse(
    status: 'success',
    code: '00',
    message: 'Success',
    vendorReference: uniqid('VND_'),
    raw: []
);
}

private function simulateTimeout(): array
{
    // simulate no response / network failure
    throw new \Exception('Connection timeout');
}

public function requery(
    Transaction $transaction
): NormalizedVendorResponse
{
   return new NormalizedVendorResponse(
    status: 'success',
    code: '00',
    message: 'Requery successful',
    vendorReference: $transaction->vendor_reference,
    raw: []
);
}

public function fetchBundles(
    string $network
): array
{
    return [

        [
            'network' => strtoupper($network),
            'product_id' => 'MOCK_1',
            'allowance' => '1GB',
            'amount' => 500,
            'validity' => '30 Days',
            'category' => 'daily',
        ],

        [
            'network' => strtoupper($network),
            'product_id' => 'MOCK_2',
            'allowance' => '2GB',
            'amount' => 1000,
            'validity' => '30 Days',
            'category' => 'daily',
        ],
    ];
}

public function validateTv(
    string $smartCardNo,
    string $provider
): array
{
    throw new \Exception(
        'TV validation not implemented'
    );
}

public function getTvSubscriptionStatus(
    string $smartCardNo,
    string $provider
): array
{
    return [

        'type' => strtoupper($provider),

        'message' => 'successful',

        'status' => '200',

        'amount' => 8883,

        'dueDate' => now()
            ->addMonth()
            ->toDateString(),
    ];
}

public function fetchTvAddons(
    string $packageCode
): array
{
    return [

        'message' => 'Successful',

        'status' => '200',

        'product' => [

            [
                'name' => 'French Plus',
                'code' => 'FRN15E36',
                'price' => 9300,
                'period' => 1,
            ],
        ],
    ];
}

public function checkTvBoxOffice(
    string $smartCardNo,
    string $provider
): array
{
    return [

        'smartCardNo' => $smartCardNo,

        'type' => strtoupper($provider),

        'message' => 'successful',

        'status' => '200',

        'isBoxOffice' => true,
    ];
}

            public function validateElectricity(
    string $meterNo,
    string $disco,
    string $type
): array
{
    throw new \Exception(
        'Electricity  has not been implemented'
    );
}

}

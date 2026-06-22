<?php

namespace App\Services\Vendors;

use App\Models\Transaction;
use App\DataTransferObjects\TransactionRequestData;
use App\Data\Responses\NormalizedVendorResponse;

class VendifyDriver extends BaseVendorDriver implements VendorInterface
{
    public function vend(
        TransactionRequestData $payload
    ): NormalizedVendorResponse {


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
    ): NormalizedVendorResponse {

        throw new \Exception('Vendify requery not implemented yet');
    }

    public function fetchBundles(
    string $network
): array
{
    throw new \Exception(
        'Bundle fetch not implemented for Vendify'
    );
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
    throw new \Exception(
        'TV subscription status not implemented'
    );
}

public function fetchTvAddons(
    string $packageCode
): array
{
    throw new \Exception(
        'TV addons not implemented'
    );
}

public function checkTvBoxOffice(
    string $smartCardNo,
    string $provider
): array
{
    throw new \Exception(
        'TV box office check not implemented'
    );
}
}

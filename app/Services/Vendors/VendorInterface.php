<?php

namespace App\Services\Vendors;

use App\Models\Transaction;
use App\DataTransferObjects\TransactionRequestData;
use App\Data\Responses\NormalizedVendorResponse;

interface VendorInterface
{
    public function vend(
        TransactionRequestData $payload
    ): NormalizedVendorResponse;

    public function requery(
        Transaction $transaction
    ): NormalizedVendorResponse;

    public function fetchBundles(
    string $network
): array;


public function validateTv(
    string $smartCardNo,
    string $provider
): array;

public function getTvSubscriptionStatus(
    string $smartCardNo,
    string $provider
): array;

public function fetchTvAddons(
    string $packageCode
): array;

public function checkTvBoxOffice(
    string $smartCardNo,
    string $provider
): array;

public function validateElectricity(
    string $meterNo,
    string $disco,
    string $type
): array;


}

<?php

namespace App\Services;

use App\Models\Vendor;
use App\Services\Vendors\VendorDriverResolver;
use App\Models\RoutingConfig;
use Illuminate\Validation\ValidationException;

class TvValidationService
{
    public function __construct(
        protected VendorDriverResolver $resolver
    ) {
    }

  public function validate(
    string $smartCardNo,
    string $provider
): array {

    $routing = RoutingConfig::where(
        'product_type',
        'tv'
    )
    ->where(
        'network',
        strtoupper($provider)
    )
    ->where('is_active', true)
    ->firstOrFail();

    $vendorId = $routing->primary_vendor_id;

    $driver = $this->resolver->resolve(
        $vendorId
    );



    return $driver->validateTv(
        $smartCardNo,
        $provider
    );
}

public function getSubscriptionStatus(
    string $smartCardNo,
    string $provider
): array
{
    $routing = RoutingConfig::where(
        'product_type',
        'tv'
    )
    ->where(
        'network',
        strtoupper($provider)
    )
    ->where('is_active', true)
    ->firstOrFail();

    $driver = $this->resolver->resolve(
        $routing->primary_vendor_id
    );

    return $driver->getTvSubscriptionStatus(
        $smartCardNo,
        $provider
    );
}

public function fetchAddons(
    string $packageCode
): array
{
    //  dd([
    //     'method' => 'fetchAddons',
    //     'packageCode' => $packageCode,
    // ]);
    $routing = RoutingConfig::where(
        'product_type',
        'tv'
    )
    ->where(
        'network',
        'DSTV'
    )
    ->where('is_active', true)
    ->firstOrFail();

    $driver = $this->resolver->resolve(
        $routing->primary_vendor_id
    );

    return $driver->fetchTvAddons(
        $packageCode
    );
}
public function checkBoxOffice(
    string $smartCardNo,
    string $provider
): array
{
    $routing = RoutingConfig::where(
        'product_type',
        'tv'
    )
    ->where(
        'network',
        strtoupper($provider)
    )
    ->where('is_active', true)
    ->firstOrFail();

    $driver = $this->resolver->resolve(
        $routing->primary_vendor_id
    );

    return $driver->checkTvBoxOffice(
        $smartCardNo,
        $provider
    );
}
public function findPackage(
    string $provider,
    string $smartCardNo,
    string $packageCode
): array {

    $packages = $this->validate(
        $smartCardNo,
        $provider
    );

    $product = $packages['product'] ?? [];

    $package = collect($product)
        ->firstWhere(
            'code',
            $packageCode
        );

    if (! $package) {

        throw ValidationException::withMessages([
            'package_code' => [
                sprintf(
                    'Package [%s] not found.',
                    $packageCode
                ),
            ],
        ]);
    }

    return $package;
}
}

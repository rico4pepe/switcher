<?php

namespace App\Services\Vendors;

use App\Data\Responses\NormalizedVendorResponse;

abstract class BaseVendorDriver
{
    protected function success(
        string $message,
        ?string $vendorReference = null,
        array $raw = [],
        string $code = '00'
    ): NormalizedVendorResponse {

        return new NormalizedVendorResponse(
            status: 'success',
            code: $code,
            message: $message,
            vendorReference: $vendorReference,
            raw: $raw,
        );
    }

    protected function failed(
        string $message,
        string $code = 'FAILED',
        array $raw = []
    ): NormalizedVendorResponse {

        return new NormalizedVendorResponse(
            status: 'failed',
            code: $code,
            message: $message,
            vendorReference: null,
            raw: $raw,
        );
    }

    protected function pending(
        string $message,
        ?string $vendorReference = null,
        array $raw = [],
        string $code = 'PENDING'
    ): NormalizedVendorResponse {

        return new NormalizedVendorResponse(
            status: 'pending',
            code: $code,
            message: $message,
            vendorReference: $vendorReference,
            raw: $raw,
        );
    }
}

<?php

namespace App\Services\Vendors;

abstract class BaseVendorDriver
{
    protected function success(
        string $message,
        ?string $vendorReference = null,
        array $raw = [],
        string $code = '00'
    ): array {

        return [
            'status' => 'success',
            'code' => $code,
            'message' => $message,
            'vendor_reference' => $vendorReference,
            'raw' => $raw,
        ];
    }

    protected function failed(
        string $message,
        string $code = 'FAILED',
        array $raw = []
    ): array {

        return [
            'status' => 'failed',
            'code' => $code,
            'message' => $message,
            'vendor_reference' => null,
            'raw' => $raw,
        ];
    }

    protected function pending(
        string $message,
        ?string $vendorReference = null,
        array $raw = [],
        string $code = 'PENDING'
    ): array {

        return [
            'status' => 'pending',
            'code' => $code,
            'message' => $message,
            'vendor_reference' => $vendorReference,
            'raw' => $raw,
        ];
    }
}

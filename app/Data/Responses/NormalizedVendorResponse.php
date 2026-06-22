<?php

namespace App\Data\Responses;

class NormalizedVendorResponse
{
    public function __construct(
        protected string $status,
        protected string $code,
        protected string $message,
        protected ?string $vendorReference = null,
        protected array $raw = [],
    ) {}

    public function status(): string
    {
        return $this->status;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function vendorReference(): ?string
    {
        return $this->vendorReference;
    }

    public function raw(): array
    {
        return $this->raw;
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRetryable(): bool
    {
        return in_array($this->code, [
            'TIMEOUT',
            'NETWORK_ERROR',
            'UNKNOWN',
        ]);
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'code' => $this->code,
            'message' => $this->message,
            'vendor_reference' => $this->vendorReference,
            'raw' => $this->raw,
        ];
    }
}

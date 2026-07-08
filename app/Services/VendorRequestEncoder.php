<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Validation\ValidationException;

class VendorRequestEncoder
{
    /**
     * Encode the Switcher transaction reference into the
     * vendor-specific request identifier.
     */
  public function encode(
    string $driverKey,
    int $transactionId,
    string $ringoReference
): string {

        return match (strtolower($driverKey)) {

         'vendify' => $this->encodeVendify(
    $transactionId
),

'oatek' => $this->encodeOatek(
    $ringoReference
),

            default => throw ValidationException::withMessages([
                'vendor' => [
                    "Unsupported vendor driver: {$driverKey}"
                ]
            ]),
        };
    }

    /**
     * Oatek accepts an alphanumeric request ID.
     */
protected function encodeOatek(
    string $ringoReference
): string
{
    return $ringoReference;
}

    /**
     * Vendify requires a numeric tracking ID.
     */
  protected function encodeVendify(
    int $transactionId
): string
{
    return str_pad(
        (string) $transactionId,
        12,
        '0',
        STR_PAD_LEFT
    );
}
}

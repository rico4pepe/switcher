<?php

namespace App\Support;

class VendorProductNormalizer
{
    /**
     * Normalize a vendor bundle into Switcher's standard format.
     */
 public function normalize(array $bundle): array
{
    $displayName = trim($bundle['allowance'] ?? '');

    $metadata = $this->extractProductMetadata($displayName);

    return [
        ...$bundle,

        /*
        |--------------------------------------------------------------------------
        | Preserve Original Vendor Name
        |--------------------------------------------------------------------------
        */
        'display_name' => $displayName,

        /*
        |--------------------------------------------------------------------------
        | Canonical Network
        |--------------------------------------------------------------------------
        */
        'network' => $this->normalizeNetwork(
            $bundle['network'] ?? null
        ),

        /*
        |--------------------------------------------------------------------------
        | Canonical Allowance
        |--------------------------------------------------------------------------
        */
        'allowance' => $metadata['allowance']
    ?? $displayName,

        /*
        |--------------------------------------------------------------------------
        | Canonical Validity
        |--------------------------------------------------------------------------
        | Prefer vendor supplied validity.
        | Otherwise use extracted validity.
        */
        'validity' => $this->normalizeValidity(
            $bundle['validity']
                ?? $metadata['validity']
        ),

        /*
        |--------------------------------------------------------------------------
        | Canonical Category
        |--------------------------------------------------------------------------
        */
        'category' => $this->normalizeCategory(
            $bundle['category']
                ?? $metadata['category']
        ),
    ];
}

    /**
     * Normalize network names.
     */
    private function normalizeNetwork(?string $network): ?string
    {
        return empty($network)
            ? null
            : strtoupper(trim($network));
    }








    /**
     * Normalize category.
     */
    private function normalizeCategory(?string $category): ?string
    {
        return empty($category)
            ? null
            : strtoupper(trim($category));
    }

/**
 * Extract canonical product metadata from the vendor product name.
 *
 * Examples:
 *  - 1GB Daily    => 1GB, 1 Day
 *  - 500MB Weekly => 500MB, 7 Days
 *  - 5GB Monthly  => 5GB, 30 Days
 */
private function extractProductMetadata(string $productName): array
{
    $productName = strtoupper(trim($productName));

    $allowance = null;

    if (preg_match('/(\d+(?:\.\d+)?\s?(?:MB|GB|TB))/i', $productName, $matches)) {
        $allowance = strtoupper(trim($matches[1]));
    }

    $validity = null;

    if (str_contains($productName, 'DAILY')) {
        $validity = 1;
    } elseif (str_contains($productName, 'WEEKLY')) {
        $validity = 7;
    } elseif (str_contains($productName, 'MONTHLY')) {
        $validity = 30;
    } elseif (str_contains($productName, 'QUARTERLY')) {
        $validity = 90;
    } elseif (
        str_contains($productName, 'YEARLY')
        || str_contains($productName, 'ANNUAL')
    ) {
        $validity = 365;
    }

    $category = null;

    if (str_contains($productName, 'SME')) {
        $category = 'SME';
    } elseif (str_contains($productName, 'CORPORATE')) {
        $category = 'CORPORATE';
    } elseif (str_contains($productName, 'SOCIAL')) {
        $category = 'SOCIAL';
    } elseif (str_contains($productName, 'NIGHT')) {
        $category = 'NIGHT';
    }

    return [
        'allowance' => $allowance,
        'validity' => $validity,
        'category' => $category,
    ];
}
private function normalizeValidity(mixed $validity): ?int
{
    if ($validity === null || $validity === '') {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Already numeric
    |--------------------------------------------------------------------------
    */

    if (is_numeric($validity)) {
        return (int) $validity;
    }

    $validity = strtoupper(trim($validity));

    /*
    |--------------------------------------------------------------------------
    | Extract leading number
    |--------------------------------------------------------------------------
    */

    if (preg_match('/(\d+)/', $validity, $matches)) {
        return (int) $matches[1];
    }

    /*
    |--------------------------------------------------------------------------
    | Named periods
    |--------------------------------------------------------------------------
    */

    return match (true) {

        str_contains($validity, 'DAILY') => 1,

        str_contains($validity, 'WEEKLY') => 7,

        str_contains($validity, 'MONTHLY') => 30,

        str_contains($validity, 'QUARTERLY') => 90,

        str_contains($validity, 'YEARLY')
        || str_contains($validity, 'ANNUAL') => 365,

        default => null,
    };
}

}

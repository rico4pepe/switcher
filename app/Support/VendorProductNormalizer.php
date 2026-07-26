<?php

namespace App\Support;

class VendorProductNormalizer
{
    /**
     * Normalize a vendor bundle into Switcher's standard format.
     */
    public function normalize(array $bundle): array
{
    $metadata = $this->extractProductMetadata(
        $bundle['allowance'] ?? ''
    );

    return [
        ...$bundle,

        'network' => $this->normalizeNetwork(
            $bundle['network'] ?? null
        ),

        'allowance' => $metadata['allowance'],

        'validity' => $metadata['validity'],

        'category' => $this->normalizeCategory(
            $bundle['category'] ?? null
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
     * Normalize data allowance.
     */
    private function normalizeAllowance(?string $allowance): ?string
    {
        if (empty($allowance)) {
            return null;
        }

        $allowance = strtoupper(trim($allowance));

        return str_replace(' ', '', $allowance);
    }




    /**
     * Normalize validity.
     */
    private function normalizeValidity(
        mixed $validity
    ): ?string {

        if (empty($validity)) {
            return null;
        }

        return strtoupper(trim((string) $validity));
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

private function extractProductMetadata(string $productName): array
{
    return [
        'allowance' => $this->normalizeAllowance($productName),
        'validity'  => null,
    ];
}

}

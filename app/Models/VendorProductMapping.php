<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorProductMapping extends Model
{
    protected $fillable = [
        'vendor_id',
        'product_id',
        'vendor_product_code',
        'vendor_product_name',
        'vendor_metadata',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'vendor_metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(
            Vendor::class
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->where(
            'is_active',
            true
        );
    }

    public function scopeVendor(
        Builder $query,
        int $vendorId
    ): Builder {
        return $query->where(
            'vendor_id',
            $vendorId
        );
    }

    public function scopeProduct(
        Builder $query,
        int $productId
    ): Builder {
        return $query->where(
            'product_id',
            $productId
        );
    }
}

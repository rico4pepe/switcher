<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'product_code',
        'product_type',
        'network',
        'display_name',
        'description',
        'amount',
        'validity',
        'category',
        'sort_order',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function vendorMappings(): HasMany
    {
        return $this->hasMany(
            VendorProductMapping::class
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

    public function scopeProductType(
        Builder $query,
        string $productType
    ): Builder {
        return $query->where(
            'product_type',
            strtolower($productType)
        );
    }

    public function scopeNetwork(
        Builder $query,
        string $network
    ): Builder {
        return $query->where(
            'network',
            strtoupper($network)
        );
    }
}

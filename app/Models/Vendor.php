<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    //
      protected $guarded = [];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeActive($query)
        {
            return $query->where('is_active', true);
        }

    public function isAvailable(): bool
    {
        return $this->is_active;
    }

    public function driver()
{
    return app(
        \App\Services\Vendors\VendorDriverRegistry::class
    )->resolve($this->driver_key);
}


}

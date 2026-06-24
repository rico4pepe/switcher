<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientRoutingConfig extends Model
{
    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(
            Client::class
        );
    }

    public function primaryVendor()
    {
        return $this->belongsTo(
            Vendor::class,
            'primary_vendor_id'
        );
    }

    public function fallbackVendor()
    {
        return $this->belongsTo(
            Vendor::class,
            'fallback_vendor_id'
        );
    }
}

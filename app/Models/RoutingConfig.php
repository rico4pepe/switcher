<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutingConfig extends Model
{
    //


    protected $guarded = [];

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

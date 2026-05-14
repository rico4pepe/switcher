<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //
      protected $guarded = [];

    protected $casts = [
            'raw_vendor_request' => 'array',
            'raw_vendor_response' => 'array',
            'resolved_at' => 'datetime',
        ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function events()
    {
        return $this->hasMany(TransactionEvent::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    //
     protected $guarded = [];

     protected $casts = [
    'is_active' => 'boolean',
    'last_used_at' => 'datetime',
];
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function routingConfigs()
{
    return $this->hasMany(
        ClientRoutingConfig::class
    );
}


}

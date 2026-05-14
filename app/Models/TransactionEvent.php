<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionEvent extends Model
{
    //

    protected $guarded = [];

      protected $casts = [
        'context' => 'array',
         'meta' => 'array',
    ];


    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

}

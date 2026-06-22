<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //
      protected $guarded = [];

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

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

            public function isPending(): bool
        {
            return $this->status === self::STATUS_PENDING;
        }

        public function isSuccessful(): bool
        {
            return $this->status === self::STATUS_SUCCESS;
        }

        public function isFailed(): bool
        {
            return $this->status === self::STATUS_FAILED;
        }

        public function isTerminal(): bool
        {
            return in_array($this->status, [
                self::STATUS_SUCCESS,
                self::STATUS_FAILED,
            ]);
        }

                    public function markPending(): void
                {
                    if ($this->isTerminal()) {

                        throw new \RuntimeException(
                            'Cannot transition terminal transaction back to pending.'
                        );
                    }

                    $this->update([
                        'status' => self::STATUS_PENDING,
                    ]);
                }

                public function markSuccessful(): void
                    {
                        if ($this->isFailed()) {

                            throw new \RuntimeException(
                                'Cannot transition failed transaction to success.'
                            );
                        }

                        $this->update([
                            'status' => self::STATUS_SUCCESS,
                            'resolved_at' => now(),
                        ]);
                    }

                public function markFailed(): void
                    {
                        if ($this->isSuccessful()) {

                            throw new \RuntimeException(
                                'Cannot transition successful transaction to failed.'
                            );
                        }

                        $this->update([
                            'status' => self::STATUS_FAILED,
                            'resolved_at' => now(),
                        ]);
                    }

}

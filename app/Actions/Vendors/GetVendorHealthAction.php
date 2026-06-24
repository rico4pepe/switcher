<?php

namespace App\Actions\Vendors;

use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class GetVendorHealthAction
{
    public function execute(?string $search = null)
    {
        return Vendor::query()
            ->when($search, function ($query) use ($search) {

                        $query->where(function ($query) use ($search) {

                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%")
                                ->orWhere('driver_key', 'like', "%{$search}%");

                        });

                    })

            ->withCount([
                'transactions as transactions_count' => fn ($query)
                => $query->whereDate(
                    'created_at',
                    today()
                ),

                'transactions as successful_transactions_count' => fn ($query)
                        => $query
                            ->whereDate('created_at', today())
                            ->where('status', 'success'),

                'transactions as failed_transactions_count' => fn ($query)
                    => $query
                        ->whereDate('created_at', today())
                        ->where('status', 'failed'),

                'transactions as pending_transactions_count' => fn ($query)
                        => $query
                            ->whereDate('created_at', today())
                            ->where('status', 'pending'),
                            ])

           ->withAvg([
                    'transactions as transactions_avg_response_time_ms' => fn ($query)
                        => $query
                            ->whereDate('created_at', today())
                            ->where('status', 'success')
                ], 'response_time_ms')

            ->get();
    }
}

<?php

namespace App\Actions\Vendors;

use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class GetVendorHealthAction
{
    public function execute()
    {
        return Vendor::query()

            ->withCount([
                'transactions',

                'transactions as successful_transactions_count' => fn ($query)
                    => $query->where('status', 'success'),

                'transactions as failed_transactions_count' => fn ($query)
                    => $query->where('status', 'failed'),

                'transactions as pending_transactions_count' => fn ($query)
                    => $query->where('status', 'pending'),
            ])

            ->withAvg(
                'transactions',
                'response_time_ms'
            )

            ->get();
    }
}

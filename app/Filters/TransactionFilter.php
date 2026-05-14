<?php

namespace App\Filters;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class TransactionFilter
{
    public function __construct(
        protected Request $request
    ) {
    }

    public function apply(
        Builder $query
    ): Builder {

        return $query

            ->when(
                $this->request->filled('reference'),
                fn ($query) => $query->where(
                    'tracking_id',
                    'like',
                    '%' . $this->request->reference . '%'
                )
            )

            ->when(
                $this->request->filled('status'),
                fn ($query) => $query->where(
                    'status',
                    strtolower($this->request->status)
                )
            )

            ->when(
                $this->request->filled('vendor'),
                fn ($query) => $query->whereHas(
                    'vendor',
                    fn ($vendorQuery) => $vendorQuery->where(
                        'name',
                        $this->request->vendor
                    )
                )
            );
    }
}

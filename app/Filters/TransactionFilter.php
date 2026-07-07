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

       $hasFilters = collect([
        'reference',
        'status',
        'customer',
         'client',
        'vendor',
        'service',
         'from',
        'to',
    ])->contains(
        fn ($field) => $this->request->filled($field)
    );

    if (! $hasFilters) {

        $query->whereDate(
            'created_at',
            today()
        );
    }

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
                $this->request->filled('customer'),

                fn ($query) => $query->where(
                    'beneficiary',
                    'like',
                    '%' . $this->request->customer . '%'
                )
            )

            ->when(
                $this->request->filled('client'),

                fn ($query) => $query->where(
                    'client_id',
                    $this->request->client
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
            )
            ->when(
                                $this->request->filled('service'),

                                fn ($query) => $query->where(
                                    'product_type',
                                    strtolower($this->request->service)
                                )
                            )
                        ->when(
                    $this->request->filled('from'),

                    fn ($query) => $query->whereDate(
                        'created_at',
                        '>=',
                        $this->request->from
                    )
                )

                ->when(
                    $this->request->filled('to'),

                    fn ($query) => $query->whereDate(
                        'created_at',
                        '<=',
                        $this->request->to
                    )
                );
    }
}

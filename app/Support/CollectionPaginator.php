<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CollectionPaginator
{
    public static function paginate(
        Collection $items,
        int $perPage = 10,
        ?int $page = null
    ): LengthAwarePaginator {

        $page = $page ?: LengthAwarePaginator::resolveCurrentPage();

        $results = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }
}

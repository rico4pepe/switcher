<?php

namespace App\Actions\Routing;

use App\Models\RoutingConfig;

class GetRoutingConfigurationsAction
{
    public function execute()
    {
        return RoutingConfig::query()
            ->with([
                'primaryVendor',
                'fallbackVendor',
            ])
            ->latest()
            ->get();
    }
}
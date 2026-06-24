<?php

namespace App\Services;

use App\Models\RoutingConfig;
use App\Models\ClientRoutingConfig;

class RoutingResolver
{
    public function resolve(
        int $clientId,
        string $productType,
        string $network
    ): RoutingConfig|ClientRoutingConfig {

        $clientRoute = ClientRoutingConfig::query()

            ->where('client_id', $clientId)

            ->where('product_type', $productType)

            ->where('network', $network)

            ->where('is_active', true)

            ->first();

        if ($clientRoute) {
            return $clientRoute;
        }

        return RoutingConfig::query()

            ->where('product_type', $productType)

            ->where('network', $network)

            ->where('is_active', true)

            ->firstOrFail();
    }
}

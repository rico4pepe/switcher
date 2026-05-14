<?php

namespace App\Actions\Routing;

use App\Models\RoutingConfig;

class UpdateRoutingConfigurationAction
{
    public function execute(
        RoutingConfig $routingConfig,
        array $data
    ): RoutingConfig {

        $routingConfig->update([
            'primary_vendor_id' => $data['primary_vendor_id'],
            'fallback_vendor_id' => $data['fallback_vendor_id'] ?: null,
            'mode' => $data['mode'],
            'is_active' => $data['is_active'],
        ]);

        return $routingConfig->fresh([
            'primaryVendor',
            'fallbackVendor',
        ]);
    }
}

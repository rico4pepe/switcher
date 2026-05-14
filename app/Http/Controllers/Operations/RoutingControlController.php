<?php

namespace App\Http\Controllers\Operations;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Actions\Routing\GetRoutingConfigurationsAction;
use Illuminate\Http\RedirectResponse;
use App\Models\RoutingConfig;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Actions\Routing\UpdateRoutingConfigurationAction;
class RoutingControlController extends Controller
{
    public function index(
        GetRoutingConfigurationsAction $getRoutingConfigurationsAction
    ): View {

        $routes = $getRoutingConfigurationsAction->execute();

        return view('operations.routing.index', [
            'routes' => $routes,
        ]);
    }

    public function toggleMode(
    RoutingConfig $routingConfig
): RedirectResponse {

    $routingConfig->update([
        'mode' => $routingConfig->mode === 'manual'
            ? 'auto'
            : 'manual',
    ]);

    return redirect()
        ->route('operations.routing.index')
        ->with(
            'success',
            'Routing mode updated successfully.'
        );
}

public function show(
    RoutingConfig $routingConfig
): View {

    $routingConfig->load([
        'primaryVendor',
        'fallbackVendor',
    ]);

    $vendors = Vendor::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    return view('operations.routing.show', [
        'route' => $routingConfig,
        'vendors' => $vendors,
    ]);
}

public function update(
    Request $request,
    RoutingConfig $routingConfig,
    UpdateRoutingConfigurationAction $updateRoutingConfigurationAction
): RedirectResponse {

    $validated = $request->validate([
        'primary_vendor_id' => [
            'required',
            'exists:vendors,id',
            'different:fallback_vendor_id',
        ],

        'fallback_vendor_id' => [
            'nullable',
            'exists:vendors,id',
            'different:primary_vendor_id',
        ],

        'mode' => [
            'required',
            'in:manual,auto',
        ],

        'is_active' => [
            'required',
            'boolean',
        ],
    ]);

    if (
        $validated['mode'] === 'auto'
        && empty($validated['fallback_vendor_id'])
    ) {
        return back()
            ->withErrors([
                'fallback_vendor_id' =>
                    'Fallback vendor is required in AUTO mode.',
            ])
            ->withInput();
    }

    $updateRoutingConfigurationAction->execute(
        $routingConfig,
        $validated
    );

    return redirect()
        ->route('operations.routing.show', $routingConfig)
        ->with(
            'success',
            'Routing configuration updated successfully.'
        );
}

}

<?php

namespace App\Http\Controllers\Operations;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Actions\Vendors\GetVendorHealthAction;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\Vendors\VendorDriverRegistry;
class VendorHealthController extends Controller
{
    public function index(
        GetVendorHealthAction $getVendorHealthAction
    ): View {

        $vendors = $getVendorHealthAction->execute(
                    request('search')
                );

        return view('operations.vendors.index', [
            'vendors' => $vendors,
        ]);
    }


    public function show(
    Vendor $vendor
): View {

   $vendor->load([
    'transactions' => fn ($query) => $query
        ->whereDate('created_at', today())
        ->latest()
        ->limit(20),
]);


$todayTransactions = $vendor->transactions()
    ->whereDate('created_at', today());

   $totalTransactions = (clone $todayTransactions)->count();

   $successfulTransactions = (clone $todayTransactions)
    ->where('status', 'success')
    ->count();

   $failedTransactions = (clone $todayTransactions)
    ->where('status', 'failed')
    ->count();

   $pendingTransactions = (clone $todayTransactions)
    ->where('status', 'pending')
    ->count();

$averageLatency = round(
    (
        (clone $todayTransactions)
            ->where('status', 'success')
            ->avg('response_time_ms')
        ?? 0
    ) / 1000,
    2
);

   $successRate = $totalTransactions > 0
    ? ($successfulTransactions / $totalTransactions) * 100
    : 0;

    return view('operations.vendors.show', [
        'vendor' => $vendor,
        'totalTransactions' => $totalTransactions,
        'successfulTransactions' => $successfulTransactions,
        'failedTransactions' => $failedTransactions,
        'pendingTransactions' => $pendingTransactions,
        'averageLatency' => $averageLatency,
        'successRate' => $successRate,
    ]);
}

public function toggle(
    Vendor $vendor
): RedirectResponse {

    $vendor->update([
        'is_active' => ! $vendor->is_active,
    ]);

    return redirect()
        ->route('operations.vendors.show', $vendor)
        ->with(
            'success',
            $vendor->is_active
                ? 'Vendor enabled successfully.'
                : 'Vendor disabled successfully.'
        );
}

public function create(): View
{
    return view('operations.vendors.create', [
        'drivers' => VendorDriverRegistry::drivers(),
    ]);
}

public function store(
    Request $request
): RedirectResponse {

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'slug' => ['required', 'string', 'max:255', 'unique:vendors,slug'],
         'driver_key' => [
        'required',
        'in:' . implode(
            ',',
            array_keys(
                VendorDriverRegistry::drivers()
            )
        ),
    ],
        'base_url' => ['nullable', 'url'],
        'description' => ['nullable', 'string'],

    ]);

    Vendor::create([
        'name' => $validated['name'],
        'slug' => $validated['slug'],
         'driver_key' => $validated['driver_key'],
        'base_url' => $validated['base_url'] ?? null,
        'description' => $validated['description'] ?? null,
        'is_active' => true,
    ]);

    return redirect()
        ->route('operations.vendors.index')
        ->with(
            'success',
            'Vendor created successfully.'
        );
}

}

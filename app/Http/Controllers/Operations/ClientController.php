<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\Clients\GetClientsAction;
use Illuminate\View\View;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    //
    public function index(
    GetClientsAction $getClientsAction
): View {

    $clients = $getClientsAction->execute();

    return view(
        'operations.clients.index',
        compact('clients')
    );
}

public function show(
    Client $client
): View {

    $todayTransactions = $client->transactions()
        ->whereDate('created_at', today());

    $totalTransactions = (clone $todayTransactions)
        ->count();

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
        ? round(
            ($successfulTransactions / $totalTransactions) * 100,
            1
        )
        : 0;

$client->load([
    'routingConfigs.primaryVendor',
    'routingConfigs.fallbackVendor',
]);

    return view(
        'operations.clients.show',
        compact(
            'client',
            'totalTransactions',
            'successfulTransactions',
            'failedTransactions',
            'pendingTransactions',
            'averageLatency',
            'successRate'
        )
    );
}

public function create(): View
{
    return view('operations.clients.create');
}

public function store(
    Request $request
): RedirectResponse {

    $validated = $request->validate([

        'organization_name' => [
            'required',
            'string',
            'max:255',
        ],

        'name' => [
            'required',
            'string',
            'max:100',
            'unique:clients,name',
        ],

        'email' => [
            'nullable',
            'email',
            'max:255',
        ],

        'contact_person' => [
            'nullable',
            'string',
            'max:255',
        ],

        'phone' => [
            'nullable',
            'string',
            'max:50',
        ],

    ]);

    $client = Client::create([

        'organization_name' => $validated['organization_name'],

        'name' => strtoupper($validated['name']),

        'email' => $validated['email'] ?? null,

        'contact_person' => $validated['contact_person'] ?? null,

        'phone' => $validated['phone'] ?? null,

        'api_key' => Str::random(64),

        'is_active' => true,

    ]);

    return redirect()
        ->route('operations.clients.show', $client)
        ->with(
            'success',
            'Client created successfully.'
        );
}

public function edit(Client $client): View
{

    return view(
        'operations.clients.edit',
        compact('client')
    );
}

public function update(
    Request $request,
    Client $client
): RedirectResponse {

    $validated = $request->validate([

        'organization_name' => [
            'required',
            'string',
            'max:255',
        ],

        'name' => [
            'required',
            'string',
            'max:100',
            Rule::unique('clients')
                ->ignore($client->id),
        ],

        'email' => [
            'nullable',
            'email',
            'max:255',
        ],

        'contact_person' => [
            'nullable',
            'string',
            'max:255',
        ],

        'phone' => [
            'nullable',
            'string',
            'max:50',
        ],

        // 'is_active' => [
        //     'required',
        //     'boolean',
        // ],

    ]);

    $client->update([

        'organization_name' => $validated['organization_name'],

        'name' => strtoupper(
            trim($validated['name'])
        ),

        'email' => $validated['email'] ?? null,

        'contact_person' => $validated['contact_person'] ?? null,

        'phone' => $validated['phone'] ?? null,

       // 'is_active' => $validated['is_active'],

    ]);

    return redirect()
        ->route(
            'operations.clients.show',
            $client
        )
        ->with(
            'success',
            'Client updated successfully.'
        );
}


public function toggle(
    Client $client
): RedirectResponse {

    $client->update([
        'is_active' => ! $client->is_active,
    ]);

    return redirect()
        ->route(
            'operations.clients.show',
            $client
        )
        ->with(
            'success',
            $client->is_active
                ? 'Client enabled successfully.'
                : 'Client disabled successfully.'
        );
}

public function regenerateKey(
    Client $client
): RedirectResponse {

    $client->update([
        'api_key' => Str::random(64),
    ]);

    return redirect()
        ->route(
            'operations.clients.show',
            $client
        )
        ->with(
            'success',
            'API key regenerated successfully.'
        );
}

}

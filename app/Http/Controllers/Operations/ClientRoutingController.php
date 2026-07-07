<?php

namespace App\Http\Controllers\Operations;


use App\Models\Vendor;
use App\Http\Controllers\Controller;
use App\Models\ClientRoutingConfig;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use App\Models\Client;

class ClientRoutingController extends Controller
{
  public function edit(
    ClientRoutingConfig $clientRoutingConfig
): View {

    $clientRoutingConfig->load([
        'client',
        'primaryVendor',
        'fallbackVendor',
    ]);

    $vendors = Vendor::where(
        'is_active',
        true
    )
    ->orderBy('name')
    ->get();

    return view(
        'operations.client-routing.edit',
        compact(
            'clientRoutingConfig',
            'vendors'
        )
    );
}

   public function update(
    Request $request,
    ClientRoutingConfig $clientRoutingConfig
): RedirectResponse {

    $validated = $request->validate([

       'primary_vendor_id' => [
    'required',
    Rule::exists('vendors', 'id')
        ->where('is_active', true),
],

      'fallback_vendor_id' => [
    'nullable',
    'different:primary_vendor_id',
    Rule::exists('vendors', 'id')
        ->where('is_active', true),
],

        'is_active' => [
            'required',
            'boolean',
        ],

    ]);

    $clientRoutingConfig->update($validated);

    return redirect()
        ->route(
            'operations.clients.show',
            $clientRoutingConfig->client
        )
        ->with(
            'success',
            'Client routing updated successfully.'
        );
}

public function create(
    Client $client
): View {

    $vendors = Vendor::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    $products = [
        'airtime',
        'data',
        'tv',
        'electricity',
        'betting',
    ];

    $networks = [
        'mtn',
        'airtel',
        'glo',
        '9mobile',
        'dstv',
        'gotv',
        'startimes',
        'ikeja_electric',
        'eko_electric',
        'abuja_electric',
        'kano_electric',
        'ibadan_electric',
        'jos_electric',
        'kaduna_electric',
        'portharcourt_electric',
        'enugu_electric',
        'bet9ja',
        'sportybet',
        'nairabet',
    ];

    return view(
        'operations.client-routing.create',
        compact(
            'client',
            'vendors',
            'products',
            'networks'
        )
    );
}

public function store(
    Request $request,
    Client $client
): RedirectResponse {

    $validated = $request->validate([

        'product_type' => [
            'required',
            Rule::in([
                'airtime',
                'data',
                'tv',
                'electricity',
                'betting',
            ]),
        ],

        'network' => [
            'required',
            'string',
            'max:100',
        ],

        'primary_vendor_id' => [
            'required',
            Rule::exists('vendors', 'id')
                ->where('is_active', true),
        ],

        'fallback_vendor_id' => [
            'nullable',
            'different:primary_vendor_id',
            Rule::exists('vendors', 'id')
                ->where('is_active', true),
        ],

        'is_active' => [
            'required',
            'boolean',
        ],

    ]);

    $exists = ClientRoutingConfig::query()
        ->where('client_id', $client->id)
        ->where('product_type', $validated['product_type'])
        ->where('network', $validated['network'])
        ->exists();

    if ($exists) {

        return back()
            ->withErrors([
                'network' => 'A routing configuration already exists for this product and network.',
            ])
            ->withInput();
    }

    ClientRoutingConfig::create([

        'client_id' => $client->id,

        'product_type' => $validated['product_type'],

        'network' => $validated['network'],

        'primary_vendor_id' => $validated['primary_vendor_id'],

        'fallback_vendor_id' => $validated['fallback_vendor_id'],

        'is_active' => $validated['is_active'],

    ]);

    return redirect()
        ->route(
            'operations.clients.show',
            $client
        )
        ->with(
            'success',
            'Client routing created successfully.'
        );
}
}

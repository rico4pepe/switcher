@extends('layouts.operations')

@section('content')

<x-operations.page-header
    :title="$client->organization_name ?? $client->name"
    description="Client operational overview."
>

<x-slot name="actions">

    <div class="flex items-center gap-3">

        <a
            href="{{ route('operations.clients.index') }}"
            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            Back
        </a>

        <a
            href="{{ route('operations.clients.edit', $client) }}"
            class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
        >
            Edit Client
        </a>

        <form
            method="POST"
            action="{{ route('operations.clients.toggle', $client) }}"
        >
            @csrf

            <button
                type="submit"
                class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium text-white
                    {{ $client->is_active
                        ? 'bg-red-600 hover:bg-red-700'
                        : 'bg-emerald-600 hover:bg-emerald-700'
                    }}"
            >
                {{ $client->is_active
                    ? 'Disable Client'
                    : 'Enable Client'
                }}
            </button>

        </form>

    </div>

</x-slot>

</x-operations.page-header>

<div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-6">

       <x-operations.stat-card
        title="Transactions"
        :value="$totalTransactions"
    />

   <x-operations.stat-card
        title="Successful"
        :value="$successfulTransactions"
    />

  <x-operations.stat-card
        title="Failed"
        :value="$failedTransactions"
    />

    <x-operations.stat-card
        title="Pending"
        :value="$pendingTransactions"
    />

      <x-operations.stat-card
        title="Success Rate"
        :value="$successRate . '%'"
    />

       <x-operations.stat-card
        title="Avg Latency"
        :value="$averageLatency . ' s'"
    />

</div>


<div class="mt-6 rounded-xl border border-slate-200 bg-white p-6">

    <div class="flex items-center justify-between">

        <div>

            <h2 class="text-sm font-semibold text-slate-900">
                API Credentials
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Authentication credentials for this client.
            </p>

        </div>

        <form
            method="POST"
            action="{{ route('operations.clients.regenerate-key', $client) }}"
            onsubmit="return confirm('Regenerate API key? The current key will stop working immediately.')"
        >
            @csrf

            <button
                type="submit"
                class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600"
            >
                Regenerate Key
            </button>

        </form>

    </div>

    <div class="mt-6">

        <label class="mb-2 block text-sm font-medium text-slate-700">
            API Key
        </label>

        <div class="flex items-center gap-3">

            <input
                id="api-key"
                type="password"
                readonly
                value="{{ $client->api_key }}"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2 text-sm font-mono"
            >

            <button
                type="button"
                onclick="toggleApiKey()"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50"
            >
                Show
            </button>

            <button
                type="button"
                onclick="copyApiKey()"
                class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white hover:bg-slate-800"
            >
                Copy
            </button>

        </div>

    </div>

</div>

<div class="mt-6 rounded-xl border border-slate-200 bg-white p-6">

    <h2 class="text-sm font-semibold text-slate-900">
        Client Information
    </h2>

    <div class="mt-4 space-y-3">

        <div>
            <span class="font-medium text-slate-700">
                Organization:
            </span>

            {{ $client->organization_name ?? '-' }}
        </div>

        <div>
            <span class="font-medium text-slate-700">
                Email:
            </span>

            {{ $client->email ?? '-' }}
        </div>

        <div>
            <span class="font-medium text-slate-700">
                Contact Person:
            </span>

            {{ $client->contact_person ?? '-' }}
        </div>

    </div>

</div>

<div class="mt-6 rounded-xl border border-slate-200 bg-white">

    <div class="border-b border-slate-200 px-6 py-4">

        <h2 class="text-sm font-semibold text-slate-900">
            Routing Configuration
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Client-specific routing overrides.
        </p>

        <div class="border-b border-slate-200 px-6 py-4">

    <div class="flex items-center justify-between">

        <div>

            <h2 class="text-sm font-semibold text-slate-900">
                Routing Configuration
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Client-specific routing overrides.
            </p>

        </div>

        <a
            href="{{ route('operations.client-routing.create', $client) }}"
            class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
        >
            Add Route
        </a>

    </div>

</div>

    </div>

    <div class="overflow-x-auto">

        <table class="min-w-full divide-y divide-slate-200">

            <thead class="bg-slate-50">

                <tr>

                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                        Product
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                        Network
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                        Primary Vendor
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                        Fallback Vendor
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                        Status
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                        Actions
                    </th>

                </tr>

            </thead>

            <tbody class="divide-y divide-slate-100 bg-white">

                @forelse($client->routingConfigs as $route)

                    <tr>

                        <td class="px-6 py-4 text-sm text-slate-900">
                            {{ strtoupper($route->product_type) }}
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ strtoupper($route->network) }}
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $route->primaryVendor->name }}
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $route->fallbackVendor?->name ?? '-' }}
                        </td>

                        <td class="px-6 py-4">

                            <span
                                class="inline-flex rounded-full px-3 py-1 text-xs font-medium
                                {{ $route->is_active
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-red-100 text-red-700'
                                }}"
                            >
                                {{ $route->is_active ? 'ACTIVE' : 'DISABLED' }}
                            </span>

                        </td>

                        <td class="px-6 py-4">

                                <a
                                    href="{{ route('operations.client-routing.edit', $route) }}"
                                    class="text-sm font-medium text-slate-900 hover:text-slate-700"
                                >
                                    Edit
                                </a>

                            </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="5"
                            class="px-6 py-8 text-center text-sm text-slate-500">

                            No routing configuration found.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

<script>

function toggleApiKey() {

    const input = document.getElementById('api-key');

    input.type = input.type === 'password'
        ? 'text'
        : 'password';
}

function copyApiKey() {

    const input = document.getElementById('api-key');

    navigator.clipboard.writeText(input.value);

    window.dispatchEvent(
        new CustomEvent('notify', {
            detail: {
                message: 'API key copied successfully.'
            }
        })
    );
}

</script>

@endsection

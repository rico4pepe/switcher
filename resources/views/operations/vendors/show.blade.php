@extends('layouts.operations')

@section('content')

    <x-operations.page-header
        :title="$vendor->name"
        description="Vendor operational overview and transaction visibility."
    >

       <x-slot name="actions">

    <div class="flex items-center gap-3">

        <a
            href="{{ route('operations.vendors.index') }}"
            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            Back
        </a>

        <form
            method="POST"
            action="{{ route('operations.vendors.toggle', $vendor) }}"
        >
            @csrf

            <button
                type="submit"
                class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium text-white
                    {{ $vendor->is_active
                        ? 'bg-red-600 hover:bg-red-700'
                        : 'bg-emerald-600 hover:bg-emerald-700'
                    }}"
            >
                {{ $vendor->is_active ? 'Disable Vendor' : 'Enable Vendor' }}
            </button>

        </form>

    </div>

</x-slot>

    </x-operations.page-header>

    {{-- Summary Cards --}}
    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-5">

        <x-operations.stat-card
            title="Total Transactions"
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
            title="Avg Latency"
            :value="$averageLatency . ' ms'"
        />

    </div>

    {{-- Vendor Health --}}
    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6">

        <div class="flex items-center justify-between">

            <div>

                <h2 class="text-sm font-semibold text-slate-900">
                    Operational Health
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Current vendor performance overview.
                </p>

            </div>

            <x-operations.vendor-health-badge
                :rate="$successRate"
            />

        </div>

        <div class="mt-6">

            <p class="text-3xl font-bold text-slate-900">
                {{ number_format($successRate, 1) }}%
            </p>

            <p class="mt-1 text-sm text-slate-500">
                Success Rate
            </p>

        </div>

    </div>

    {{-- Operational State --}}

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6">

     <div class="flex items-center justify-between">

        <div>

            <h2 class="text-sm font-semibold text-slate-900">
                Operational State
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Current vendor routing availability.
            </p>

        </div>

        <span
            class="inline-flex rounded-full px-3 py-1 text-sm font-medium
                {{ $vendor->is_active
                    ? 'bg-emerald-100 text-emerald-700'
                    : 'bg-red-100 text-red-700'
                }}"
        >
            {{ $vendor->is_active ? 'ACTIVE' : 'DISABLED' }}
        </span>

    </div>

</div>

    {{-- Recent Transactions --}}
    <div class="mt-6 rounded-xl border border-slate-200 bg-white">

        <div class="border-b border-slate-200 px-6 py-4">

            <h2 class="text-sm font-semibold text-slate-900">
                Recent Transactions
            </h2>

        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-slate-200">

                <thead class="bg-slate-50">

                    <tr>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Tracking ID
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Network
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Amount
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Status
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Response Time
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">

                    @foreach ($vendor->transactions as $transaction)

                        <tr>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">
                                {{ $transaction->tracking_id }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ strtoupper($transaction->network) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                ₦{{ number_format($transaction->amount, 2) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">

                                <x-operations.status-badge
                                    :status="$transaction->status"
                                />

                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ $transaction->response_time_ms ?? 'N/A' }} ms
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

@endsection

@extends('layouts.operations')

@section('content')

    <div class="p-4 sm:p-6">

        {{-- Page Header --}}
       <x-operations.page-header
            title="Transactions"
            description="Operational visibility into routed transactions."
        >
            <x-slot name="actions">

                <button
                    type="button"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                >
                    Refresh
                </button>

            </x-slot>

        </x-operations.page-header>

        {{-- KPI Cards --}}
       <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">

    <x-operations.stat-card
        title="Total Transaction"
        :value="number_format($metrics['totalToday'])"
    />

    <x-operations.stat-card
        title="Successful"
        :value="number_format($metrics['successfulToday'])"
        valueClass="text-emerald-600"
    />

    <x-operations.stat-card
        title="Failed"
        :value="number_format($metrics['failedToday'])"
        valueClass="text-rose-600"
    />

    <x-operations.stat-card
        title="Pending"
        :value="number_format($metrics['pendingToday'])"
        valueClass="text-amber-600"
    />

   <x-operations.stat-card
    title="Success Rate"
    :value="$metrics['successRate'] . '%'"
    valueClass="text-emerald-600"
/>
<x-operations.stat-card
    title="Avg Latency"
    :value="$metrics['avgLatency'] . 's'"
    valueClass="text-sky-600"
/>

</div>

<div class="mt-6 rounded-xl border border-slate-200 bg-white">

    <div class="border-b border-slate-200 px-6 py-4">

        <h2 class="text-sm font-semibold text-slate-900">
            Transaction Filters
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Search and filter operational transactions.
        </p>

    </div>

    <form
        method="GET"
        class="p-6"
    >

        {{-- Row 1 --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

            <x-operations.filter-input
                name="reference"
                label="Reference"
                placeholder="Tracking ID"
                :value="request('reference')"
            />

            <x-operations.filter-input
                name="customer"
                label="Customer"
                placeholder="080..."
                :value="request('customer')"
            />

            <x-operations.filter-select
                name="client"
                label="Client"
            >

                <option value="">All Clients</option>

                @foreach($clients as $client)

                    <option
                        value="{{ $client->id }}"
                        @selected(request('client') == $client->id)
                    >
                        {{ $client->organization_name }}
                    </option>

                @endforeach

            </x-operations.filter-select>

            <x-operations.filter-select
                name="status"
                label="Status"
            >

                <option value="">All Statuses</option>

                <option value="success" @selected(request('status')=='success')>
                    Success
                </option>

                <option value="failed" @selected(request('status')=='failed')>
                    Failed
                </option>

                <option value="pending" @selected(request('status')=='pending')>
                    Pending
                </option>

            </x-operations.filter-select>

        </div>

        {{-- Row 2 --}}
        <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">

            <x-operations.filter-select
                name="vendor"
                label="Vendor"
            >

                <option value="">All Vendors</option>

                @foreach($vendors as $vendor)

                    <option
                        value="{{ $vendor->name }}"
                        @selected(request('vendor') == $vendor->name)
                    >
                        {{ $vendor->name }}
                    </option>

                @endforeach

            </x-operations.filter-select>

            <x-operations.filter-select
                name="service"
                label="Service"
            >

                <option value="">All Services</option>

                <option
                    value="airtime"
                    @selected(request('service')=='airtime')
                >
                    Airtime
                </option>

                <option
                    value="data"
                    @selected(request('service')=='data')
                >
                    Data
                </option>

                <option
                    value="tv"
                    @selected(request('service')=='tv')
                >
                    TV
                </option>

                <option
                    value="electricity"
                    @selected(request('service')=='electricity')
                >
                    Electricity
                </option>

                <option
                    value="betting"
                    @selected(request('service')=='betting')
                >
                    Betting
                </option>

            </x-operations.filter-select>

            <div>

                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Date Range
                </label>

                <div class="flex items-center gap-2">

                    <input
                        type="date"
                        name="from"
                        value="{{ request('from') }}"
                        class="w-full rounded-lg border-slate-300 text-sm"
                    >

                    <span class="text-slate-400">
                        →
                    </span>

                    <input
                        type="date"
                        name="to"
                        value="{{ request('to') }}"
                        class="w-full rounded-lg border-slate-300 text-sm"
                    >

                </div>

            </div>

        </div>

        {{-- Actions --}}
        <div class="mt-6 flex flex-wrap items-center justify-between">

            <div class="flex gap-2">

                {{-- Reserved for Sprint 1 --}}
                <a
        href="{{ route('operations.transactions.export.csv', request()->query()) }}"
        class="inline-flex items-center rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100"
    >
        Export CSV
    </a>
                {{-- Export Excel --}}

            </div>

            <div class="flex gap-3">

                <a
                    href="{{ route('operations.transactions.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Reset
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-800"
                >
                    Apply Filters
                </button>

            </div>

        </div>

    </form>

</div>
                {{-- Transactions Table --}}

            <x-operations.table>
                 <x-slot name="pagination">
        {{ $transactions->links() }}
    </x-slot>

    <x-operations.table-head>

        <tr>

            <x-operations.table-th>
                Reference
            </x-operations.table-th>

            <x-operations.table-th>
                Customer
            </x-operations.table-th>

            <x-operations.table-th>
                Service
            </x-operations.table-th>

            <x-operations.table-th>
                Amount
            </x-operations.table-th>

            <x-operations.table-th>
                Vendor
            </x-operations.table-th>

            <x-operations.table-th>
                Status
            </x-operations.table-th>

            <x-operations.table-th>
                Created
            </x-operations.table-th>

            <x-operations.table-th class="text-right">
                Actions
            </x-operations.table-th>

        </tr>

    </x-operations.table-head>

    <x-operations.table-body>

        {{-- @php
            $statuses = [
                'SUCCESS',
                'FAILED',
                'PENDING',
                'PROCESSING',
                'RETRIED',
            ];

            $transactions = range(1, 10);
        @endphp --}}

       @forelse ($transactions as $transaction)

            <x-operations.table-row>

                <x-operations.table-td class="font-medium text-slate-900">
                    {{ $transaction['tracking_id'] }}
                </x-operations.table-td>

                <x-operations.table-td>
                  {{ $transaction->beneficiary ?? 'N/A' }}
                </x-operations.table-td>

                <x-operations.table-td>
                   {{ ucfirst($transaction->product_type) }}
                </x-operations.table-td>

                <x-operations.table-td>
                   ₦{{ number_format($transaction->amount, 2) }}
                </x-operations.table-td>

                <x-operations.table-td>
                 {{ $transaction->vendor?->name ?? 'Unassigned' }}
                </x-operations.table-td>

                <x-operations.table-td>
                    <x-operations.status-badge
                      :status="$transaction->status"
                    />
                </x-operations.table-td>

                <x-operations.table-td class="text-slate-500">
                   {{ $transaction->created_at->diffForHumans() }}
                </x-operations.table-td>

                <x-operations.table-td class="text-right">

                    <div class="relative inline-block text-left">

                      <a
                        href="{{ route('operations.transactions.show', $transaction) }}"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50"
                    >
                        View
                    </a>

                    </div>

                </x-operations.table-td>

            </x-operations.table-row>

        @empty

            <tr>

                <td colspan="8" class="px-6 py-16 text-center">

                    <div class="mx-auto max-w-sm">

                        <h3 class="text-sm font-semibold text-slate-900">
                            No transactions found
                        </h3>

                        <p class="mt-2 text-sm text-slate-500">
                            Transactions matching the selected filters will appear here.
                        </p>

                    </div>

                </td>

            </tr>

        @endforelse

    </x-operations.table-body>

</x-operations.table>


    </div>

@endsection

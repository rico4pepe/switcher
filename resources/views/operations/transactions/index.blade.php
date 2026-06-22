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
        title="Total Today"
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
        title="Requeries"
        value="42"
        valueClass="text-violet-600"
    />

    <x-operations.stat-card
        title="Failovers"
        value="17"
        valueClass="text-sky-600"
    />

</div>

                {{-- Filters --}}
                <form method="GET">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">

           <x-operations.filter-input
            name="reference"
            label="Reference"
            placeholder="TXN-12345"
            :value="request('reference')"
        />

            <x-operations.filter-input
                name="customer"
                label="Customer"
                placeholder="080..."
                :value="request('customer')"
            />

           <x-operations.filter-select
    name="status"
    label="Status"
>
            <option value="">All</option>

                    <option
                    value="success"
                    @selected(request('status') === 'success')
                >
                    Success
                </option>

                <option
                    value="failed"
                    @selected(request('status') === 'failed')
                >
                    Failed
                </option>

                <option
                    value="pending"
                    @selected(request('status') === 'pending')
                >
                    Pending
                </option>
            </x-operations.filter-select>

<x-operations.filter-select
    name="vendor"
    label="Vendor"
>
    <option value="">All</option>

    

    @foreach ($vendors as $vendor)

        <option
            value="{{ $vendor->name }}"
            @selected(request('vendor') === $vendor->name)
        >
            {{ $vendor->name }}
        </option>

    @endforeach

</x-operations.filter-select>

<x-operations.filter-select
    name="service"
    label="Service"
>
    <option value="">All</option>

    <option
        value="airtime"
        @selected(request('service') === 'airtime')
    >
        Airtime
    </option>

    <option
        value="data"
        @selected(request('service') === 'data')
    >
        Data
    </option>
</x-operations.filter-select>
            <div>

                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">
                    Date
                </label>

                <input
    type="date"
    name="date"
    value="{{ request('date') }}"
    class="w-full rounded-lg border-slate-300 text-sm focus:border-slate-400 focus:ring-slate-400"
>

            </div>

            <div class="flex items-end">

    <button
        type="submit"
        class="w-full rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
    >
        Apply Filters
    </button>

</div>

        </div>
        </form>
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

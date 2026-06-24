@extends('layouts.operations')

@section('content')

  <x-operations.page-header
    title="Vendor Health"
    description="Today's operational performance across all vendors."
>

    <x-slot name="actions">

        <a
            href="{{ route('operations.vendors.create') }}"
            class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
        >
            Add Vendor
        </a>

    </x-slot>

</x-operations.page-header>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white">

        <div class="border-b border-slate-200 p-4">

    <form method="GET">

        <div class="max-w-md">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search vendor..."
                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-slate-500 focus:outline-none"
            >

        </div>

    </form>

</div>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-slate-200">

                <thead class="bg-slate-50">

                    <tr>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Vendor
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Health
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Total
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Success
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Failed
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Pending
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Avg Latency
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Success Rate
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">

                    @foreach ($vendors as $vendor)

                        @php
                            $successRate = $vendor->transactions_count > 0
                                ? ($vendor->successful_transactions_count / $vendor->transactions_count) * 100
                                : 0;
                        @endphp

                        <tr>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">
                               <a
                                href="{{ route('operations.vendors.show', $vendor) }}"
                                class="font-medium text-slate-900 hover:text-slate-700"
                            >
                                {{ $vendor->name }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">

                                        <x-operations.vendor-health-badge
                                            :rate="$successRate"
                                        />

                                    </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ $vendor->transactions_count }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-emerald-600">
                                {{ $vendor->successful_transactions_count }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-red-600">
                                {{ $vendor->failed_transactions_count }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-amber-600">
                                {{ $vendor->pending_transactions_count }}
                            </td>

                                                    @php
                            $latency = round($vendor->transactions_avg_response_time_ms ?? 0);

                            $latencyClass = match (true) {
                                $latency <= 3000 => 'text-emerald-600',
                                $latency <= 7000 => 'text-amber-600',
                                default => 'text-red-600',
                            };
                        @endphp

                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium {{ $latencyClass }}">
                            {{ $latency }} ms
                        </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">
                                {{ number_format($successRate, 1) }}%
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>




        </div>

    </div>

@endsection

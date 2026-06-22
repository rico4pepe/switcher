@extends('layouts.operations')

@section('content')

    <div class="p-4 sm:p-6">

        <x-operations.page-header
            :title="$transaction->tracking_id"
            description="Transaction operational details and investigation."
        >

            <x-slot name="actions">

    <div class="flex items-center gap-3">

        <a
            href="{{ route('operations.transactions.index') }}"
            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            Back
        </a>

        @if ($transaction->status === 'pending')

            <form
                method="POST"
                action="{{ route('operations.transactions.requery', $transaction) }}"
            >
                @csrf

                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                >
                    Requery Transaction
                </button>

            </form>

        @endif

    </div>

</x-slot>

        </x-operations.page-header>

        <div class="rounded-xl border border-slate-200 bg-white p-6">

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">

                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Status
                    </p>

                    <div class="mt-2">
                        <x-operations.status-badge
                            :status="$transaction->status"
                        />
                    </div>
                </div>

                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Amount
                    </p>

                    <p class="mt-2 text-lg font-semibold text-slate-900">
                        ₦{{ number_format($transaction->amount, 2) }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Vendor
                    </p>

                    <p class="mt-2 text-sm font-medium text-slate-900">
                        {{ $transaction->vendor?->name ?? 'Unassigned' }}
                    </p>
                </div>

               <div>

    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
        Response Time
    </p>

    @php

        $latency = $transaction->response_time_ms;

        $latencyColor = match (true) {

            $latency === null => 'text-slate-500',

            $latency < 2000 => 'text-green-600',

            $latency < 5000 => 'text-yellow-600',

            default => 'text-red-600',
        };

    @endphp

    <p class="mt-2 text-sm font-semibold {{ $latencyColor }}">

        {{ $latency ? number_format($latency) . ' ms' : 'N/A' }}

    </p>

</div>

            </div>

        </div>

        <div class="mt-6 rounded-xl border border-slate-200 bg-white">

            <div class="border-b border-slate-200 px-6 py-4">

                <h2 class="text-sm font-semibold text-slate-900">
                    Transaction Metadata
                </h2>

            </div>

            <div class="grid grid-cols-1 gap-6 px-6 py-6 md:grid-cols-2 xl:grid-cols-3">

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">
                        Tracking ID
                    </p>

                    <p class="mt-2 text-sm font-medium text-slate-900 break-all">
                        {{ $transaction->tracking_id }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">
                        Ringo Reference
                    </p>

                    <p class="mt-2 text-sm font-medium text-slate-900 break-all">
                        {{ $transaction->ringo_reference }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">
                        Vendor Reference
                    </p>

                    <p class="mt-2 text-sm font-medium text-slate-900">
                        {{ $transaction->vendor_reference ?? 'N/A' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">
                        Client
                    </p>

                    <p class="mt-2 text-sm font-medium text-slate-900">
                        {{ $transaction->client?->name ?? 'N/A' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">
                        Vendor
                    </p>

                    <p class="mt-2 text-sm font-medium text-slate-900">
                        {{ $transaction->vendor?->name ?? 'Unassigned' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">
                        Product Type
                    </p>

                    <p class="mt-2 text-sm font-medium text-slate-900">
                        {{ ucfirst($transaction->product_type) }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">
                        Network
                    </p>

                    <p class="mt-2 text-sm font-medium text-slate-900">
                        {{ strtoupper($transaction->network) }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">
                        Beneficiary
                    </p>

                    <p class="mt-2 text-sm font-medium text-slate-900">
                        {{ $transaction->beneficiary ?? 'N/A' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">
                        Created
                    </p>

                    <p class="mt-2 text-sm font-medium text-slate-900">
                        {{ $transaction->created_at->format('M d, Y h:i:s A') }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">
                        Resolved
                    </p>

                    <p class="mt-2 text-sm font-medium text-slate-900">
                        {{ $transaction->resolved_at?->format('M d, Y h:i:s A') ?? 'Pending' }}
                    </p>
                </div>

                <div>
    <p class="text-xs uppercase tracking-wide text-slate-500">
        Pending Age
    </p>

    <p class="mt-2 text-sm font-medium text-slate-900">

        @if ($transaction->status === 'pending')

            {{ $transaction->created_at->diffForHumans() }}

        @else

            —

        @endif

    </p>
</div>

            </div>

</div>


<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">

    {{-- Vendor Request --}}
    <div class="rounded-xl border border-slate-200 bg-white">

        <div class="border-b border-slate-200 px-6 py-4">

            <h2 class="text-sm font-semibold text-slate-900">
                Vendor Request Payload
            </h2>

        </div>

        <div class="overflow-x-auto p-6">

            <pre class="overflow-x-auto rounded-lg bg-slate-50 p-4 text-xs leading-6 text-slate-700">{{ json_encode($transaction->raw_vendor_request, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

        </div>

    </div>

    {{-- Vendor Response --}}
    <div class="rounded-xl border border-slate-200 bg-white">

        <div class="border-b border-slate-200 px-6 py-4">

            <h2 class="text-sm font-semibold text-slate-900">
                Vendor Response Payload
            </h2>

        </div>

        <div class="overflow-x-auto p-6">

            <pre class="overflow-x-auto rounded-lg bg-slate-50 p-4 text-xs leading-6 text-slate-700">{{ json_encode($transaction->raw_vendor_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

        </div>

    </div>

</div>

<div class="mt-6 rounded-xl border border-slate-200 bg-white">

    <div class="border-b border-slate-200 px-6 py-4">

        <h2 class="text-sm font-semibold text-slate-900">
            Transaction Timeline
        </h2>

    </div>

   <div class="relative divide-y divide-slate-100">

    @php

    $eventColors = [

        'transaction_created' => 'bg-slate-400',

        'vendor_called' => 'bg-blue-500',

        'vendor_response' => 'bg-green-500',

        'vendor_exception' => 'bg-red-500',

        'failover_triggered' => 'bg-yellow-500',

        'requery_started' => 'bg-indigo-500',

        'requery_response' => 'bg-purple-500',

        'requery_resolved' => 'bg-green-500',

        'requery_rejected' => 'bg-red-500',
    ];

@endphp

        @forelse ($transaction->events->sortByDesc('created_at') as $event)

         <div class="relative px-6 py-5">

    <div class="flex gap-4">

        {{-- Timeline Dot --}}
        <div class="mt-1.5 flex flex-col items-center">

           <div class="h-3 w-3 rounded-full {{ $eventColors[$event->event_type] ?? 'bg-slate-400' }}"></div>

            @unless ($loop->last)

                <div class="mt-2 h-full w-px bg-slate-200"></div>

            @endunless

        </div>

        {{-- Timeline Content --}}
        <div class="min-w-0 flex-1 pb-2">

            <div class="flex flex-wrap items-center gap-3">

                <x-operations.timeline-badge
                    :event="$event->event_type"
                />

                <p class="text-xs text-slate-500">
                    {{ $event->created_at->format('M d, Y h:i:s A') }}
                    <span class="text-xs text-slate-400">
                        • {{ $event->created_at->diffForHumans() }}
                    </span>
                </p>


            </div>

            @if ($event->message)

                <p class="mt-3 text-sm leading-6 text-slate-700">
                    {{ $event->message }}
                </p>

            @endif

            @if (!empty($event->meta))

                <div class="mt-4 overflow-x-auto rounded-lg bg-slate-50 p-4">

                    <pre class="text-xs leading-6 text-slate-700">{{ json_encode($event->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

                </div>

            @endif

        </div>

    </div>

</div>

        @empty

            <div class="px-6 py-12 text-center">

                <p class="text-sm text-slate-500">
                    No timeline events available.
                </p>

            </div>

        @endforelse

    </div>

</div>

    </div>

@endsection

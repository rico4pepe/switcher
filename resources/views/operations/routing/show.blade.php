@extends('layouts.operations')

@section('content')

    <x-operations.page-header
        title="Routing Configuration"
        :description="strtoupper($route->product_type) . ' / ' . strtoupper($route->network)"
    >

        <x-slot name="actions">

            <div class="flex items-center gap-3">

                <a
                    href="{{ route('operations.routing.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Back
                </a>

            </div>

        </x-slot>

    </x-operations.page-header>

    {{-- Route Overview --}}
    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">

        <x-operations.stat-card
            title="Routing Mode"
            :value="strtoupper($route->mode)"
        />

        <x-operations.stat-card
            title="Failover"
            :value="$route->auto_failover_enabled ? 'Enabled' : 'Disabled'"
        />

        <x-operations.stat-card
            title="Threshold"
            :value="$route->failover_threshold_pct . '%'"
        />

        <x-operations.stat-card
            title="Sample Size"
            :value="$route->min_sample_size"
        />

    </div>

    {{-- Routing Controls --}}
    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6">

        <div class="border-b border-slate-200 pb-4">

            <h2 class="text-sm font-semibold text-slate-900">
                Routing Controls
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Operational traffic routing configuration.
            </p>

        </div>

        <form
            method="POST"
            action="{{ route('operations.routing.update', $route) }}"
            class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2"
        >

            @csrf
            @method('PUT')

            {{-- Primary Vendor --}}
            <div>

                <label class="text-sm font-medium text-slate-700">
                    Primary Vendor
                </label>

                <select
                    name="primary_vendor_id"
                    class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-slate-500 focus:outline-none"
                >

                    @foreach ($vendors as $vendor)

                        <option
                            value="{{ $vendor->id }}"
                            @selected($route->primary_vendor_id === $vendor->id)
                        >
                            {{ $vendor->name }}
                        </option>

                    @endforeach

                </select>

            </div>

            {{-- Fallback Vendor --}}
            <div>

                <label class="text-sm font-medium text-slate-700">
                    Fallback Vendor
                </label>

                <select
                    name="fallback_vendor_id"
                    class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-slate-500 focus:outline-none"
                >

                    <option value="">
                        No Fallback
                    </option>

                    @foreach ($vendors as $vendor)

                        <option
                            value="{{ $vendor->id }}"
                            @selected($route->fallback_vendor_id === $vendor->id)
                        >
                            {{ $vendor->name }}
                        </option>

                    @endforeach

                </select>

            </div>

            {{-- Routing Mode --}}
            <div>

                <label class="text-sm font-medium text-slate-700">
                    Routing Mode
                </label>

                <select
                    name="mode"
                    class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-slate-500 focus:outline-none"
                >

                    <option
                        value="manual"
                        @selected($route->mode === 'manual')
                    >
                        MANUAL
                    </option>

                    <option
                        value="auto"
                        @selected($route->mode === 'auto')
                    >
                        AUTO
                    </option>

                </select>

            </div>

            {{-- Route Active --}}
            <div>

                <label class="text-sm font-medium text-slate-700">
                    Route State
                </label>

                <select
                    name="is_active"
                    class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-slate-500 focus:outline-none"
                >

                    <option
                        value="1"
                        @selected($route->is_active)
                    >
                        ACTIVE
                    </option>

                    <option
                        value="0"
                        @selected(! $route->is_active)
                    >
                        DISABLED
                    </option>

                </select>

            </div>

            <div class="md:col-span-2">

                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                >
                    Save Routing Configuration
                </button>

            </div>

        </form>

    </div>

@endsection

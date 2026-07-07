@extends('layouts.operations')

@section('content')

<x-operations.page-header
    title="Create Client Routing"
    description="Create a new routing configuration for this client."
>

    <x-slot name="actions">

        <a
            href="{{ route('operations.clients.show', $client) }}"
            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            Back
        </a>

    </x-slot>

</x-operations.page-header>

<form
    method="POST"
    action="{{ route('operations.client-routing.store', $client) }}"
    class="mt-6"
>

    @csrf

    <div class="rounded-xl border border-slate-200 bg-white p-6">

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            {{-- Client --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Client
                </label>

                <input
                    type="text"
                    readonly
                    value="{{ $client->organization_name }}"
                    class="w-full rounded-lg border border-slate-300 bg-slate-100 px-4 py-2"
                >

            </div>

            {{-- Product --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Product
                </label>

                <select
                    name="product_type"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2"
                >

                    <option value="">Select Product</option>

                    @foreach($products as $product)

                        <option
                            value="{{ $product }}"
                            @selected(old('product_type') == $product)
                        >
                            {{ strtoupper($product) }}
                        </option>

                    @endforeach

                </select>

                @error('product_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

            </div>

            {{-- Network --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Network
                </label>

                <select
                    name="network"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2"
                >

                    <option value="">Select Network</option>

                    @foreach($networks as $network)

                        <option
                            value="{{ $network }}"
                            @selected(old('network') == $network)
                        >
                            {{ strtoupper($network) }}
                        </option>

                    @endforeach

                </select>

                @error('network')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

            </div>

            {{-- Primary Vendor --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Primary Vendor
                </label>

                <select
                    name="primary_vendor_id"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2"
                >

                    @foreach($vendors as $vendor)

                        <option
                            value="{{ $vendor->id }}"
                            @selected(old('primary_vendor_id') == $vendor->id)
                        >
                            {{ $vendor->name }}
                        </option>

                    @endforeach

                </select>

                @error('primary_vendor_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

            </div>

            {{-- Fallback Vendor --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Fallback Vendor
                </label>

                <select
                    name="fallback_vendor_id"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2"
                >

                    <option value="">None</option>

                    @foreach($vendors as $vendor)

                        <option
                            value="{{ $vendor->id }}"
                            @selected(old('fallback_vendor_id') == $vendor->id)
                        >
                            {{ $vendor->name }}
                        </option>

                    @endforeach

                </select>

                @error('fallback_vendor_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

            </div>

            {{-- Status --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Status
                </label>

                <select
                    name="is_active"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2"
                >

                    <option value="1" selected>
                        Active
                    </option>

                    <option value="0">
                        Disabled
                    </option>

                </select>

            </div>

        </div>

        <div class="mt-8 flex justify-end">

            <button
                type="submit"
                class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-800"
            >
                Create Route
            </button>

        </div>

    </div>

</form>

@endsection

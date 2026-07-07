@extends('layouts.operations')

@section('content')

<x-operations.page-header
    title="Edit Client Routing"
    description="Update client-specific routing configuration."
>

    <x-slot name="actions">

        <a
            href="{{ route('operations.clients.show', $clientRoutingConfig->client) }}"
            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            Back
        </a>

    </x-slot>

</x-operations.page-header>

<form
    method="POST"
    action="{{ route('operations.client-routing.update', $clientRoutingConfig) }}"
    class="mt-6"
>

    @csrf
    @method('PUT')

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
                    value="{{ $clientRoutingConfig->client->organization_name }}"
                    class="w-full rounded-lg border border-slate-300 bg-slate-100 px-4 py-2"
                >

            </div>

            {{-- Product --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Product
                </label>

                <input
                    type="text"
                    readonly
                    value="{{ strtoupper($clientRoutingConfig->product_type) }}"
                    class="w-full rounded-lg border border-slate-300 bg-slate-100 px-4 py-2"
                >

            </div>

            {{-- Network --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Network
                </label>

                <input
                    type="text"
                    readonly
                    value="{{ strtoupper($clientRoutingConfig->network) }}"
                    class="w-full rounded-lg border border-slate-300 bg-slate-100 px-4 py-2"
                >

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
                            @selected($vendor->id == $clientRoutingConfig->primary_vendor_id)
                        >
                            {{ $vendor->name }}
                        </option>

                    @endforeach

                </select>

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
                            @selected($vendor->id == $clientRoutingConfig->fallback_vendor_id)
                        >
                            {{ $vendor->name }}
                        </option>

                    @endforeach

                </select>

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

                    <option
                        value="1"
                        @selected($clientRoutingConfig->is_active)
                    >
                        Active
                    </option>

                    <option
                        value="0"
                        @selected(!$clientRoutingConfig->is_active)
                    >
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
                Save Changes
            </button>

        </div>

    </div>

</form>

@endsection

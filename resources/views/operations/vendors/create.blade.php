@extends('layouts.operations')

@section('content')

    <x-operations.page-header
        title="Add Vendor"
        description="Register a new operational vendor."
    >

        <x-slot name="actions">

            <a
                href="{{ route('operations.vendors.index') }}"
                class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
            >
                Back
            </a>

        </x-slot>

    </x-operations.page-header>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6">

        <form
            method="POST"
            action="{{ route('operations.vendors.store') }}"
            class="grid grid-cols-1 gap-6"
        >

            @csrf

            {{-- Vendor Name --}}
            <div>

                <label class="text-sm font-medium text-slate-700">
                    Vendor Name
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-slate-500 focus:outline-none"
                >

            </div>

            {{-- Slug --}}
            <div>

                <label class="text-sm font-medium text-slate-700">
                    Vendor Slug
                </label>

                <input
                    type="text"
                    name="slug"
                    value="{{ old('slug') }}"
                    class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-slate-500 focus:outline-none"
                >

            </div>

            {{-- Base URL --}}
            <div>

                <label class="text-sm font-medium text-slate-700">
                    Base URL
                </label>

                <input
                    type="url"
                    name="base_url"
                    value="{{ old('base_url') }}"
                    class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-slate-500 focus:outline-none"
                >

            </div>

            {{-- Driver --}}
<div>

    <label class="text-sm font-medium text-slate-700">
        Driver
    </label>

    <select
        name="driver_key"
        class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-slate-500 focus:outline-none"
    >

        <option value="">
            Select Driver
        </option>

        @foreach ($drivers as $key => $driver)

            <option
                value="{{ $key }}"
                @selected(old('driver_key') === $key)
            >
                {{ $driver['label'] }}
            </option>

        @endforeach

    </select>

</div>

            {{-- Description --}}
            <div>

                <label class="text-sm font-medium text-slate-700">
                    Description
                </label>

                <textarea
                    name="description"
                    rows="4"
                    class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-slate-500 focus:outline-none"
                >{{ old('description') }}</textarea>

            </div>

            <div>

                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                >
                    Create Vendor
                </button>

            </div>

        </form>

    </div>

@endsection

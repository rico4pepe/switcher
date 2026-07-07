@extends('layouts.operations')

@section('content')

<x-operations.page-header
    title="Add Client"
    description="Register a new client on the Switcher platform."
>

    <x-slot name="actions">

        <a
            href="{{ route('operations.clients.index') }}"
            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            Back
        </a>

    </x-slot>

</x-operations.page-header>

<form
    method="POST"
    action="{{ route('operations.clients.store') }}"
    class="mt-6"
>
    @csrf

    <div class="rounded-xl border border-slate-200 bg-white p-6">

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            {{-- Organization Name --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Organization Name
                </label>

                <input
                    type="text"
                    name="organization_name"
                    value="{{ old('organization_name') }}"
                    required
                    class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-slate-500 focus:outline-none"
                >

                @error('organization_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

            </div>

            {{-- Client Name --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Client Name
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    placeholder="e.g UBA"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-slate-500 focus:outline-none"
                >

                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

            </div>

            {{-- Email --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-slate-500 focus:outline-none"
                >

                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

            </div>

            {{-- Contact Person --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Contact Person
                </label>

                <input
                    type="text"
                    name="contact_person"
                    value="{{ old('contact_person') }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-slate-500 focus:outline-none"
                >

                @error('contact_person')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

            </div>

            {{-- Phone --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Phone
                </label>

                <input
                    type="text"
                    name="phone"
                    value="{{ old('phone') }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-slate-500 focus:outline-none"
                >

                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

            </div>

        </div>

        <div class="mt-8 flex justify-end">

            <button
                type="submit"
                class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-800"
            >
                Create Client
            </button>

        </div>

    </div>

</form>

@endsection

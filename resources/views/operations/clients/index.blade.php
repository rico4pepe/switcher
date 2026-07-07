@extends('layouts.operations')

@section('content')

<x-operations.page-header
    title="Clients"
    description="Customer onboarding and operational visibility."
>

    <x-slot name="actions">

        <a
            href="{{ route('operations.clients.create') }}"
            class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
        >
            Add Client
        </a>

    </x-slot>

</x-operations.page-header>


<div class="mt-6 rounded-xl border border-slate-200 bg-white">

    <div class="border-b border-slate-200 px-6 py-4">

        <div class="flex items-center justify-between">

            <div>

                <h2 class="text-sm font-semibold text-slate-900">
                    Client Directory
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Registered clients and operational status.
                </p>

            </div>

        </div>

    </div>

    <div class="overflow-x-auto">

        <table class="min-w-full divide-y divide-slate-200">

            <thead class="bg-slate-50">

                <tr>

                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Organization
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Name
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Contact
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Status
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Last Activity
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Actions
                    </th>

                </tr>

            </thead>

            <tbody class="divide-y divide-slate-100 bg-white">

                @forelse($clients as $client)

                    <tr>

                        <td class="px-6 py-4 text-sm font-medium text-slate-900">
                            {{ $client->organization_name ?? '-' }}
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $client->name }}
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $client->contact_person ?? '-' }}
                        </td>

                        <td class="px-6 py-4">

                            <span
                                class="inline-flex rounded-full px-3 py-1 text-xs font-medium
                                {{ $client->is_active
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-red-100 text-red-700'
                                }}"
                            >
                                {{ $client->is_active ? 'ACTIVE' : 'DISABLED' }}
                            </span>

                        </td>

                        <td class="px-6 py-4 text-sm text-slate-600">

                            @if($client->last_used_at)
                                {{ $client->last_used_at->diffForHumans() }}
                            @else
                                Never
                            @endif

                        </td>

                        <td class="px-6 py-4">

                            <a
                                href="{{ route('operations.clients.show', $client) }}"
                                class="text-sm font-medium text-slate-900 hover:text-slate-700"
                            >
                                View
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">
                            No clients found.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection

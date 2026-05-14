@extends('layouts.operations')

@section('content')

    <x-operations.page-header
        title="Routing Control"
        description="Operational routing visibility and vendor orchestration."
    />

    <div class="mt-6 rounded-xl border border-slate-200 bg-white">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-slate-200">

                <thead class="bg-slate-50">

                    <tr>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Product
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Network
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Primary Vendor
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Fallback Vendor
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Mode
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Failover
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            State
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Risk
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">

                    @foreach ($routes as $route)

                        <tr>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">

                                    <a
                                        href="{{ route('operations.routing.show', $route) }}"
                                        class="text-slate-900 hover:text-slate-700"
                                    >
                                        {{ strtoupper($route->product_type) }}
                                    </a>

                                </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ strtoupper($route->network) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-900">
                                {{ $route->primaryVendor?->name ?? 'N/A' }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ $route->fallbackVendor?->name ?? 'N/A' }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">

                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium
                                        {{ $route->mode === 'auto'
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : 'bg-slate-100 text-slate-700'
                                        }}"
                                >
                                    {{ strtoupper($route->mode) }}
                                </span>

                            </td>

                            <td class="whitespace-nowrap px-6 py-4">

                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium
                                        {{ $route->auto_failover_enabled
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : 'bg-red-100 text-red-700'
                                        }}"
                                >
                                    {{ $route->auto_failover_enabled ? 'ENABLED' : 'DISABLED' }}
                                </span>

                            </td>

                            <td class="whitespace-nowrap px-6 py-4">

                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium
                                        {{ $route->is_active
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : 'bg-red-100 text-red-700'
                                        }}"
                                >
                                    {{ $route->is_active ? 'ACTIVE' : 'DISABLED' }}
                                </span>

                            </td>

                            <td class="px-6 py-4">

                                <x-operations.routing-risk-badge
                                    :route="$route"
                                />

                            </td>

                            <td class="whitespace-nowrap px-6 py-4">

                                    <form
                                        method="POST"
                                        action="{{ route('operations.routing.toggle-mode', $route) }}"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-medium text-white
                                                {{ $route->mode === 'manual'
                                                    ? 'bg-emerald-600 hover:bg-emerald-700'
                                                    : 'bg-slate-900 hover:bg-slate-800'
                                                }}"
                                        >
                                            {{ $route->mode === 'manual'
                                                ? 'Switch To AUTO'
                                                : 'Switch To MANUAL'
                                            }}
                                        </button>

                                    </form>

                                </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

@endsection

@props([
    'route',
])

@php

    $issues = [];

    if (
        $route->mode === 'auto'
        && ! $route->fallback_vendor_id
    ) {
        $issues[] = 'No fallback vendor';
    }

    if (
        $route->primaryVendor
        && ! $route->primaryVendor->is_active
    ) {
        $issues[] = 'Primary vendor inactive';
    }

    if (
        $route->fallbackVendor
        && ! $route->fallbackVendor->is_active
    ) {
        $issues[] = 'Fallback vendor inactive';
    }

    if (
        $route->primary_vendor_id
        && $route->primary_vendor_id === $route->fallback_vendor_id
    ) {
        $issues[] = 'Primary equals fallback';
    }

@endphp

@if (count($issues))

    <div class="space-y-2">

        @foreach ($issues as $issue)

            <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">
                {{ $issue }}
            </span>

        @endforeach

    </div>

@else

    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">
        Healthy
    </span>

@endif

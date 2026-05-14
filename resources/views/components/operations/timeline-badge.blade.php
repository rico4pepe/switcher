@props([
    'event',
])

@php

    $styles = match (true) {

        str_contains($event, 'exception') => 'bg-red-100 text-red-700',

        str_contains($event, 'failed') => 'bg-red-100 text-red-700',

        str_contains($event, 'retry') => 'bg-amber-100 text-amber-700',

        str_contains($event, 'failover') => 'bg-violet-100 text-violet-700',

        str_contains($event, 'resolved') => 'bg-emerald-100 text-emerald-700',

        str_contains($event, 'response') => 'bg-emerald-100 text-emerald-700',

        default => 'bg-slate-100 text-slate-700',
    };

@endphp

<span
    {{ $attributes->merge([
        'class' => "inline-flex rounded-full px-2.5 py-1 text-xs font-medium {$styles}"
    ]) }}
>
    {{ $event }}
</span>

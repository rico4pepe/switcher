@props([
    'status' => 'PENDING',
])

@php

    $classes = match (strtoupper($status)) {

        'SUCCESS' => 'bg-emerald-100 text-emerald-700',

        'FAILED' => 'bg-rose-100 text-rose-700',

        'PENDING' => 'bg-amber-100 text-amber-700',

        'PROCESSING' => 'bg-sky-100 text-sky-700',

        'RETRIED' => 'bg-violet-100 text-violet-700',

        default => 'bg-slate-100 text-slate-700',
    };

@endphp

<span {{ $attributes->merge([
    'class' => "inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {$classes}"
]) }}>
    {{ strtoupper($status) }}
</span>

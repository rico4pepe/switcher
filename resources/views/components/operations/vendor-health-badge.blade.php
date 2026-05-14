@props([
    'rate' => 0,
])

@php

    $styles = match (true) {

        $rate >= 95 => 'bg-emerald-100 text-emerald-700',

        $rate >= 80 => 'bg-amber-100 text-amber-700',

        default => 'bg-red-100 text-red-700',
    };

    $label = match (true) {

        $rate >= 95 => 'Healthy',

        $rate >= 80 => 'Degraded',

        default => 'Critical',
    };

@endphp

<span
    {{ $attributes->merge([
        'class' => "inline-flex rounded-full px-2.5 py-1 text-xs font-medium {$styles}"
    ]) }}
>
    {{ $label }}
</span>

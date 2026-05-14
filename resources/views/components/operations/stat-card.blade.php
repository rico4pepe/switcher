@props([
    'title',
    'value',
    'valueClass' => 'text-slate-900',
])

<div class="rounded-xl border border-slate-200 bg-white p-4">

    <p class="text-sm text-slate-500">
        {{ $title }}
    </p>

    <p class="mt-2 text-2xl font-semibold {{ $valueClass }}">
        {{ $value }}
    </p>

</div>

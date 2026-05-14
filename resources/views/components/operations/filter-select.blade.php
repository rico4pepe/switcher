@props([
    'label',
])

<div>

    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">
        {{ $label }}
    </label>

    <select
        {{ $attributes->merge([
            'class' => 'w-full rounded-lg border-slate-300 text-sm focus:border-slate-400 focus:ring-slate-400'
        ]) }}
    >
        {{ $slot }}
    </select>

</div>

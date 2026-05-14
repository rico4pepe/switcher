@props([
    'label',
    'placeholder' => '',
])

<div>

    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">
        {{ $label }}
    </label>

   <input
    type="text"
    value="{{ $attributes->get('value') }}"
    placeholder="{{ $placeholder }}"
    {{ $attributes->merge([
        'class' => 'w-full rounded-lg border-slate-300 text-sm focus:border-slate-400 focus:ring-slate-400'
    ]) }}
>

</div>

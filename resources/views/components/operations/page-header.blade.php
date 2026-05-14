@props([
    'title',
    'description' => null,
])

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

    <div>

        <h1 class="text-2xl font-semibold text-slate-900">
            {{ $title }}
        </h1>

        @if ($description)

            <p class="mt-1 text-sm text-slate-500">
                {{ $description }}
            </p>

        @endif

    </div>

    @if (isset($actions))

        <div>
            {{ $actions }}
        </div>

    @endif

</div>

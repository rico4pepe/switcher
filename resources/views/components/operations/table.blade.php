<div class="mt-6 rounded-xl border border-slate-200 bg-white">

    <div class="overflow-x-auto">

        <table class="min-w-full divide-y divide-slate-200">
            {{ $slot }}
        </table>

    </div>

    @isset($pagination)

        <div class="border-t border-slate-200 px-6 py-4">

            {{ $pagination }}

        </div>

    @endisset

</div>

<div class="flex h-full flex-col overflow-hidden">

    {{-- Logo / Brand --}}
    <div class="border-b border-slate-800 px-6 py-5">
        <h1 class="text-lg font-bold tracking-wide">
            Switcher Ops
        </h1>

        <p class="mt-1 text-xs text-slate-400">
            Operations Console
        </p>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 space-y-1">

        <a href="{{ route('operations.transactions.index') }}"
           class="flex items-center rounded-lg px-3 py-2 text-sm font-medium bg-slate-800 text-white">
            Dashboard
        </a>

        <a
            href="{{ route('operations.clients.index') }}"
            class="flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white"
        >
            Clients
        </a>


        <a
            href="{{ route('operations.vendors.index') }}"
            class="flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white"
        >
            Vendors
        </a>

        <a
            href="{{ route('operations.routing.index') }}"
            class="flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white"
        >
            Routing Control
        </a>

        <a href="#"
           class="flex items-center rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white transition">
            Failovers
        </a>

        <a href="#"
           class="flex items-center rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white transition">
            Scheduler Jobs
        </a>

    </nav>

</div>

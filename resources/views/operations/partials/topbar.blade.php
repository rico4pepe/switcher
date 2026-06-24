<header class="border-b border-slate-200 bg-white">

    <div class="flex h-16 items-center justify-between px-4 lg:px-6">

        {{-- Left Section --}}
        <div class="flex items-center gap-3">

            {{-- Mobile Menu Button --}}
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-md p-2 text-slate-600 hover:bg-slate-100 lg:hidden"
                @click="sidebarOpen = true"
            >
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-6 w-6"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            {{-- Desktop Collapse Button --}}
            <button
                type="button"
                class="hidden lg:inline-flex items-center justify-center rounded-md p-2 text-slate-600 hover:bg-slate-100"
                @click="sidebarCollapsed = ! sidebarCollapsed"
            >
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-5 w-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            {{-- Title --}}
            <div>
                <h2 class="text-sm font-medium text-slate-500">
                    Operations Console
                </h2>
            </div>

        </div>

        {{-- Right Section --}}
        <div class="flex items-center gap-4">

            {{-- Environment --}}
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                {{ strtoupper(app()->environment()) }}
            </span>

            {{-- User --}}
           <div class="flex items-center gap-3">

    <div class="text-sm text-slate-600">
        {{ auth()->user()->name }}
    </div>

    <form
        method="POST"
        action="{{ route('logout') }}"
    >
        @csrf

      <button
    type="submit"
    class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800"
>
    Logout
</button>
    </form>

</div>

        </div>

    </div>

</header>

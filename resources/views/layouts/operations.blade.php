<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }} - Operations</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    x-data="{
        sidebarOpen: false,
        sidebarCollapsed: false
    }"
    class="overflow-x-hidden bg-slate-100 text-slate-900 antialiased"
>

    <div class="min-h-screen flex">

       {{-- Mobile Overlay --}}
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"
                @click="sidebarOpen = false"
            ></div>

            {{-- Sidebar --}}
            <aside
                :class="sidebarCollapsed ? 'lg:w-20' : 'lg:w-64'"
                class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-slate-900 text-slate-100 transition-all duration-300
                    transform lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            >

                @include('operations.partials.sidebar')

            </aside>

        {{-- Main Content --}}
        <div
    :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'"
    class="flex min-h-screen flex-1 flex-col transition-all duration-300"
>

            {{-- Topbar --}}
            @include('operations.partials.topbar')

            {{-- Page Content --}}
                <main class="flex-1 overflow-y-auto p-4 sm:p-6">

                    @if (session('success'))

                        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">

                            {{ session('success') }}

                        </div>

                    @endif

                    @if ($errors->any())

                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">

                    <ul class="space-y-1">

                        @foreach ($errors->all() as $error)

                            <li>
                                • {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

@endif

                    @yield('content')

                </main>

        </div>

    </div>



    <div
    x-data="{ show: false, message: '' }"
    x-on:notify.window="
        message = $event.detail.message;
        show = true;
        setTimeout(() => show = false, 2500);
    "
    x-show="show"
    x-transition
    class="fixed top-6 right-6 z-50"
    style="display: none;"
>
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-lg">
        <span x-text="message"></span>
    </div>
</div>

</body>
</html>

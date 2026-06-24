<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
   <body class="font-sans antialiased bg-slate-100">

    <div class="min-h-screen flex">

        {{-- Left Branding Panel --}}
        <div class="hidden lg:flex lg:w-1/2 bg-slate-900 text-white">

            <div class="flex flex-col justify-center px-16">

                <div class="mb-6">
                    <h1 class="text-5xl font-bold tracking-wide">
                        RINGO
                    </h1>

                    <h2 class="mt-2 text-2xl font-semibold text-slate-200">
                        Switcher
                    </h2>
                    <p class="mt-1 text-sm uppercase tracking-[0.2em] text-slate-400">
                        Operations Console
                    </p>
                </div>

                <p class="max-w-md text-slate-300 leading-relaxed">
                    Vendor Orchestration Platform for intelligent routing,
                    failover management, transaction monitoring and operational visibility.
                </p>

                <div class="mt-12 space-y-3 text-sm text-slate-200">

                    <div>✓ Vendor Routing</div>

                    <div>✓ Auto Failover</div>

                    <div>✓ Transaction Monitoring</div>

                    <div>✓ Vendor Health Tracking</div>

                    <div>✓ Operations Console</div>

                </div>

            </div>

        </div>

        {{-- Login Area --}}
        <div class="flex flex-1 items-center justify-center px-6 py-12">

            <div class="w-full max-w-md">

                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-slate-900">
                        Welcome Back
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Sign in to access the Switcher Operations Console.
                    </p>
                </div>

                {{ $slot }}

            </div>

        </div>

    </div>

</body>
</html>

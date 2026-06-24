<x-guest-layout>

    <x-auth-session-status
        class="mb-6"
        :status="session('status')"
    />

    <form
        method="POST"
        action="{{ route('login') }}"
        class="space-y-6"
    >
        @csrf

        {{-- Email --}}
        <div>

            <label
                for="email"
                class="mb-2 block text-sm font-medium text-slate-700"
            >
                Email Address
            </label>

            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900"
            >

            @error('email')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror

        </div>

        {{-- Password --}}
        <div>

            <label
                for="password"
                class="mb-2 block text-sm font-medium text-slate-700"
            >
                Password
            </label>

            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900"
            >

            @error('password')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror

        </div>

        {{-- Remember Me --}}
        <div class="flex items-center justify-between">

            <label class="flex items-center gap-2">

                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                >

                <span class="text-sm text-slate-600">
                    Remember me
                </span>

            </label>

            @if (Route::has('password.request'))
                <a
                    href="{{ route('password.request') }}"
                    class="text-sm text-slate-600 hover:text-slate-900"
                >
                    Forgot password?
                </a>
            @endif

        </div>

        {{-- Login Button --}}
        <button
            type="submit"
            class="w-full rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white transition hover:bg-slate-800"
        >
            Sign In
        </button>

        {{-- Footer --}}
        <div class="text-center text-xs text-slate-400">

            Switcher Operations Console

            <span class="mx-2">•</span>

            {{ strtoupper(app()->environment()) }}

        </div>

    </form>

</x-guest-layout>

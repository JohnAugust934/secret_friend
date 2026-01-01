<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="text-center mb-8">
        <h2 class="text-3xl font-black text-gray-900 dark:text-white flex justify-center items-center gap-2 tracking-tight">
            Bem-vindo! <span class="text-3xl hover:animate-spin cursor-default">👋</span>
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Entre para descobrir seu amigo secreto.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div>
            <x-floating-input
                name="email"
                label="Endereço de E-mail"
                type="email"
                :value="old('email')"
                required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-floating-input
                name="password"
                label="Sua Senha"
                type="password"
                required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:bg-gray-800 transition group-hover:scale-110" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200 transition">{{ __('Lembrar de mim') }}</span>
            </label>

            @if (Route::has('password.request'))
            <a class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-bold transition underline decoration-transparent hover:decoration-indigo-500" href="{{ route('password.request') }}">
                {{ __('Esqueceu?') }}
            </a>
            @endif
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center text-sm py-4 shadow-xl shadow-indigo-500/20">
                {{ __('Entrar na Conta') }}
            </x-primary-button>
        </div>

        <div class="pt-4 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Não tem conta?
                <a href="{{ route('register') }}" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline transition">
                    Crie uma grátis
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
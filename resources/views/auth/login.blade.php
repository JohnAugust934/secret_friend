<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="text-center mb-6">
        {{-- ADICIONADO: dark:text-white para ficar visível no modo escuro --}}
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex justify-center items-center gap-2">
            Bem-vindo de volta! <span class="text-2xl">👋</span>
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Insira os seus dados para entrar.</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <label for="email" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">E-mail</label>
            <input id="email" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                placeholder="exemplo@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label for="password" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Senha</label>
            <input id="password" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                type="password"
                name="password"
                required autocomplete="current-password"
                placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:bg-gray-900" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Lembrar de mim') }}</span>
            </label>

            @if (Route::has('password.request'))
            <a class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-bold transition" href="{{ route('password.request') }}">
                {{ __('Esqueceu a senha?') }}
            </a>
            @endif
        </div>

        <div class="mt-6">
            <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-indigo-200 dark:shadow-none transition transform hover:scale-105">
                {{ __('Entrar na Conta') }}
            </button>
        </div>

        <div class="mt-6 border-t border-gray-100 dark:border-gray-700 pt-4 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Ainda não tem conta?
                <a href="{{ route('register') }}" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                    Crie uma agora
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
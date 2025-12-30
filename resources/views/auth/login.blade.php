<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-800">Bem-vindo de volta! 👋</h2>
        <p class="text-sm text-gray-500 mt-1">Insira os seus dados para entrar.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" value="E-mail" class="text-gray-600 font-semibold" />
            <x-text-input id="email" class="block mt-1 w-full bg-gray-50 border-gray-200 focus:border-indigo-500 focus:bg-white transition h-11" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="exemplo@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Senha" class="text-gray-600 font-semibold" />
            <x-text-input id="password" class="block mt-1 w-full bg-gray-50 border-gray-200 focus:border-indigo-500 focus:bg-white transition h-11"
                type="password"
                name="password"
                required autocomplete="current-password"
                placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Lembrar de mim</span>
            </label>

            @if (Route::has('password.request'))
            <a class="text-sm text-indigo-600 hover:text-indigo-800 font-medium hover:underline" href="{{ route('password.request') }}">
                Esqueceu a senha?
            </a>
            @endif
        </div>

        <div class="mt-6">
            <button type="submit" class="w-full justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 duration-200">
                Entrar na Conta
            </button>
        </div>

        <div class="mt-6 text-center border-t border-gray-100 pt-4">
            <p class="text-sm text-gray-500">
                Ainda não tem conta?
                <a href="{{ route('register') }}" class="text-indigo-600 font-bold hover:underline">
                    Crie uma agora
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
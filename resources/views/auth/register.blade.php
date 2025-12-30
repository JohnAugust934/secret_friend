<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-800">Crie sua conta 🚀</h2>
        <p class="text-sm text-gray-500 mt-1">Junte-se à diversão do Amigo Secreto.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" value="Nome Completo" class="text-gray-600 font-semibold" />
            <x-text-input id="name" class="block mt-1 w-full bg-gray-50 border-gray-200 focus:border-indigo-500 focus:bg-white transition h-11" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Seu nome" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" value="E-mail" class="text-gray-600 font-semibold" />
            <x-text-input id="email" class="block mt-1 w-full bg-gray-50 border-gray-200 focus:border-indigo-500 focus:bg-white transition h-11" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="exemplo@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Senha" class="text-gray-600 font-semibold" />
            <x-text-input id="password" class="block mt-1 w-full bg-gray-50 border-gray-200 focus:border-indigo-500 focus:bg-white transition h-11"
                type="password"
                name="password"
                required autocomplete="new-password" placeholder="Mínimo 8 caracteres" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirmar Senha" class="text-gray-600 font-semibold" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full bg-gray-50 border-gray-200 focus:border-indigo-500 focus:bg-white transition h-11"
                type="password"
                name="password_confirmation" required autocomplete="new-password" placeholder="Repita a senha" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <button type="submit" class="w-full justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 duration-200">
                Registar Conta
            </button>
        </div>

        <div class="mt-6 text-center border-t border-gray-100 pt-4">
            <p class="text-sm text-gray-500">
                Já tem conta?
                <a href="{{ route('login') }}" class="text-indigo-600 font-bold hover:underline">
                    Fazer Login
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
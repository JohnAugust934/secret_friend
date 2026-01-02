<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
        {{ __('Obrigado por se cadastrar! Antes de começar, por favor verifique seu endereço de e-mail clicando no link que acabamos de enviar. Se você não recebeu o e-mail, teremos prazer em enviar outro.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
    <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
        {{ __('Um novo link de verificação foi enviado para o e-mail informado no cadastro.') }}
    </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Reenviar E-mail de Verificação') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition">
                {{ __('Sair') }}
            </button>
        </form>
    </div>
</x-guest-layout>
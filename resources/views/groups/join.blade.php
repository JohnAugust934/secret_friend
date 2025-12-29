<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Convite para Amigo Secreto
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg border-t-4 border-indigo-500">
                <div class="p-8 text-center">

                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Você foi convidado!</h3>
                    <p class="text-gray-600 mb-6">
                        Você está prestes a entrar no grupo <br>
                        <span class="text-indigo-600 font-bold text-xl">"{{ $group->name }}"</span>
                    </p>

                    <div class="bg-gray-50 p-4 rounded-lg text-left text-sm text-gray-600 mb-6">
                        <p><strong>📅 Data:</strong> {{ $group->event_date->format('d/m/Y') }}</p>
                        <p><strong>💰 Valor:</strong> R$ {{ number_format($group->budget, 2, ',', '.') }}</p>
                        <p class="mt-2 italic">"{{ Str::limit($group->description, 100) }}"</p>
                    </div>

                    <form action="{{ route('groups.join.store', $group->invite_token) }}" method="POST">
                        @csrf

                        <div class="mb-6 text-left">
                            <label for="wishlist" class="block text-sm font-medium text-gray-700 mb-1">
                                O que você gostaria de ganhar? (Sua lista de desejos)
                            </label>
                            <textarea name="wishlist" id="wishlist" rows="3" placeholder="Ex: Livros de ficção, chinelo 40, chocolate..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <a href="{{ route('dashboard') }}" class="block w-full py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </a>
                            <button type="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Confirmar Entrada
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
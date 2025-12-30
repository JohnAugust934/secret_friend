<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            Criar Novo Grupo
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 relative">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full opacity-50 blur-2xl pointer-events-none"></div>

                <div class="p-6 sm:p-10 relative z-10">
                    <header class="mb-8">
                        <h3 class="text-lg font-bold text-indigo-900 uppercase tracking-wide mb-2">Detalhes do Evento</h3>
                        <p class="text-gray-500">Defina as regras e a data para a revelação.</p>
                    </header>

                    <form action="{{ route('groups.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Nome do Grupo')" class="text-base font-semibold text-gray-700 ml-1" />
                            <x-text-input id="name" class="block mt-2 w-full h-12 rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition" type="text" name="name" :value="old('name')" required autofocus placeholder="Ex: Natal da Família Silva" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="event_date" :value="__('Data da Revelação')" class="text-base font-semibold text-gray-700 ml-1" />
                                <x-text-input id="event_date" class="block mt-2 w-full h-12 rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition" type="date" name="event_date" :value="old('event_date')" required />
                                <x-input-error :messages="$errors->get('event_date')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="budget" :value="__('Valor Máximo (Opcional)')" class="text-base font-semibold text-gray-700 ml-1" />
                                <div class="relative mt-2">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">R$</span>
                                    <x-text-input id="budget" class="block w-full h-12 pl-10 rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition" type="number" step="0.01" name="budget" :value="old('budget')" placeholder="0,00" />
                                </div>
                                <x-input-error :messages="$errors->get('budget')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Regras / Descrição')" class="text-base font-semibold text-gray-700 ml-1" />
                            <textarea id="description" name="description" rows="3" class="block mt-2 w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition" placeholder="Ex: O presente deve ser algo criativo...">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="bg-indigo-50 p-5 rounded-xl border border-indigo-100">
                            <x-input-label for="wishlist" :value="__('O que você gostaria de ganhar?')" class="text-base font-bold text-indigo-800 ml-1 mb-1" />
                            <x-text-input id="wishlist" class="block mt-2 w-full h-12 rounded-xl border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500 bg-white" type="text" name="wishlist" :value="old('wishlist')" placeholder="Ex: Um livro de ficção, meias coloridas..." />
                            <p class="text-xs text-indigo-400 mt-2 ml-1">Isso ajuda quem tirar você no sorteio!</p>
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-4">
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-gray-800">Cancelar</a>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5">
                                Criar Grupo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
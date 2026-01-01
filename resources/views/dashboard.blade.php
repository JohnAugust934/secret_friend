<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight drop-shadow-sm">
                {{ __('Meus Grupos') }}
            </h2>
            <a href="{{ route('groups.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-indigo-500/30 transition transform hover:-translate-y-0.5 active:scale-95 flex items-center gap-2">
                <span>+</span> Criar Grupo
            </a>
        </div>
    </x-slot>

    <div class="py-8 pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- BLOCO DE ALERTAS ANTIGO REMOVIDO --}}

            @if($groups->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($groups as $group)
                <a href="{{ route('groups.show', $group) }}" class="block group">
                    <div class="h-full relative bg-white/60 dark:bg-gray-800/60 backdrop-blur-md rounded-3xl shadow-xl hover:shadow-2xl hover:shadow-indigo-500/10 border border-white/40 dark:border-gray-700/50 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">

                        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>

                        <div class="p-6 flex flex-col h-full">
                            <div class="flex justify-between items-start mb-4">
                                <div class="bg-indigo-50/80 dark:bg-indigo-900/30 p-2.5 rounded-xl text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                @if($group->is_drawn)
                                <span class="bg-green-100/90 dark:bg-green-900/60 text-green-700 dark:text-green-300 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider shadow-sm backdrop-blur-sm">Sorteado</span>
                                @else
                                <span class="bg-yellow-100/90 dark:bg-yellow-900/60 text-yellow-700 dark:text-yellow-300 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider shadow-sm backdrop-blur-sm">Pendente</span>
                                @endif
                            </div>

                            <h3 class="font-bold text-xl text-gray-800 dark:text-white mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition line-clamp-1">{{ $group->name }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm line-clamp-2 leading-relaxed mb-6 flex-1">{{ $group->description }}</p>

                            <div class="flex items-center text-xs font-bold text-gray-400 uppercase tracking-wider gap-4 border-t border-gray-100 dark:border-gray-700/50 pt-4">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $group->event_date->format('d/m') }}
                                </div>
                                <div class="flex items-center gap-1 text-green-600 dark:text-green-400">
                                    <span>R$ {{ number_format($group->budget, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-20 bg-white/40 dark:bg-gray-800/40 backdrop-blur-lg rounded-3xl border border-dashed border-gray-300 dark:border-gray-700">
                <div class="bg-indigo-50/50 dark:bg-indigo-900/30 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-indigo-500 dark:text-indigo-400 animate-bounce">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-2">Sem grupos ainda</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-sm mx-auto">Comece a diversão criando o seu primeiro grupo de Amigo Secreto!</p>
                <a href="{{ route('groups.create') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-8 rounded-2xl shadow-xl shadow-indigo-500/20 transition transform hover:scale-105 active:scale-95">
                    Criar Primeiro Grupo
                </a>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
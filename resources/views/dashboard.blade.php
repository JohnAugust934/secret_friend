<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Meus Grupos') }}
            </h2>
            <a href="{{ route('groups.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded-lg shadow transition flex items-center gap-2">
                <span>+</span> Criar Grupo
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
            <div class="bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-200 p-4 mb-6 shadow-sm rounded-r" role="alert">
                <p class="font-bold">Sucesso</p>
                <p>{{ session('success') }}</p>
            </div>
            @endif

            @if($groups->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($groups as $group)
                <a href="{{ route('groups.show', $group) }}" class="block group">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 dark:border-gray-700 transition-all duration-300 h-full flex flex-col overflow-hidden relative">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-purple-500 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>

                        <div class="p-6 flex-1">
                            <div class="flex justify-between items-start mb-4">
                                <div class="bg-indigo-50 dark:bg-indigo-900/30 p-2 rounded-lg text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                @if($group->is_drawn)
                                <span class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 text-xs font-bold px-2 py-1 rounded-full uppercase tracking-wide">Sorteado</span>
                                @else
                                <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 text-xs font-bold px-2 py-1 rounded-full uppercase tracking-wide">Pendente</span>
                                @endif
                            </div>

                            <h3 class="font-bold text-xl text-gray-800 dark:text-white mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">{{ $group->name }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm line-clamp-2">{{ $group->description }}</p>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/30 px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                {{ $group->event_date->format('d/m/Y') }}
                            </div>
                            <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                                Ver Detalhes &rarr;
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="bg-indigo-50 dark:bg-indigo-900/30 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-indigo-500 dark:text-indigo-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">Você ainda não tem grupos</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">Crie seu primeiro grupo de Amigo Secreto e convide a galera para participar!</p>
                <a href="{{ route('groups.create') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-200 dark:shadow-none transition transform hover:scale-105">
                    Começar Agora
                </a>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
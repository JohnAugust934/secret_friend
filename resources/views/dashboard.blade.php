<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                Meus Grupos
            </h2>
            <a href="{{ route('groups.create') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                <span class="mr-2 text-lg">+</span> Novo Grupo
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($groups->isEmpty())
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 text-center py-16 px-6">
                <div class="bg-indigo-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Ainda não participa de nenhum grupo</h3>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">Crie o seu próprio grupo para sortear entre amigos ou peça o link de convite a alguém.</p>
                <a href="{{ route('groups.create') }}" class="text-indigo-600 font-bold hover:underline">Começar agora &rarr;</a>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($groups as $group)
                <a href="{{ route('groups.show', $group) }}" class="group relative block bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 overflow-hidden transition-all duration-300 transform hover:-translate-y-1">
                    <div class="absolute top-0 left-0 w-1 h-full {{ $group->is_drawn ? 'bg-green-500' : 'bg-yellow-400' }}"></div>

                    <div class="p-6 pl-8">
                        <div class="flex justify-between items-start mb-4">
                            <div class="bg-indigo-50 text-indigo-700 text-xs font-bold px-2 py-1 rounded uppercase tracking-wide">
                                {{ $group->event_date->format('d/m') }}
                            </div>
                            @if($group->is_drawn)
                            <span class="text-green-600 text-xs font-bold flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Sorteado
                            </span>
                            @endif
                        </div>

                        <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-indigo-600 transition">{{ $group->name }}</h3>

                        <p class="text-gray-500 text-sm line-clamp-2 mb-4 h-10">
                            {{ $group->description ?: 'Sem descrição definida.' }}
                        </p>

                        <div class="flex items-center justify-between border-t border-gray-50 pt-4 mt-2">
                            <div class="flex -space-x-2 overflow-hidden">
                                @foreach($group->members->take(3) as $member)
                                <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600" title="{{ $member->name }}">
                                    {{ substr($member->name, 0, 1) }}
                                </div>
                                @endforeach
                                @if($group->members->count() > 3)
                                <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-gray-100 flex items-center justify-center text-xs text-gray-500">
                                    +{{ $group->members->count() - 3 }}
                                </div>
                                @endif
                            </div>
                            <span class="text-xs text-gray-400 font-medium">Ver detalhes &rarr;</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
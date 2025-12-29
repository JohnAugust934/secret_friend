<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Meus Eventos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex justify-end mb-6">
                <a href="{{ route('groups.create') }}" class="w-full sm:w-auto text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded shadow transition text-lg sm:text-base">
                    + Novo Amigo Secreto
                </a>
            </div>

            @if($groups->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($groups as $group)
                <a href="{{ route('groups.show', $group) }}" class="block transform transition hover:scale-105 duration-200">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full border-l-4 {{ $group->is_drawn ? 'border-green-500' : 'border-yellow-500' }}">
                        <div class="p-6">
                            <h3 class="font-bold text-xl text-gray-800 mb-2">{{ $group->name }}</h3>
                            <p class="text-sm text-gray-500 mb-4">{{ Str::limit($group->description, 60) }}</p>

                            <div class="flex justify-between items-end mt-4">
                                <div class="text-xs text-gray-400">
                                    Data: {{ $group->event_date->format('d/m/Y') }}
                                </div>
                                <div class="text-right">
                                    @if($group->is_drawn)
                                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">Sorteado</span>
                                    @else
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded">Aberto</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-10 text-center">
                <p class="text-gray-500 text-lg mb-4">Você ainda não participa de nenhum amigo secreto.</p>
                <a href="{{ route('groups.create') }}" class="text-indigo-600 font-bold hover:underline">
                    Crie o primeiro agora!
                </a>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
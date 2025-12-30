<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ $group->name }}
            </h2>
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-indigo-600 flex items-center">
                &larr; Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-6 sm:p-8 border-b border-gray-100">
                    <div class="flex justify-between items-start">
                        <p class="text-gray-700 text-lg leading-relaxed flex-1">{{ $group->description }}</p>
                        @if($group->owner_id === auth()->id())
                        <a href="{{ route('groups.edit', $group) }}" class="ml-4 p-2 bg-white rounded-lg shadow-sm text-gray-400 hover:text-indigo-600 transition" title="Editar Informações">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                    <div class="p-6 text-center">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Data da Revelação</span>
                        <span class="text-xl font-bold text-gray-800">{{ $group->event_date->format('d/m/Y') }}</span>
                    </div>
                    <div class="p-6 text-center">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Valor Máximo</span>
                        <span class="text-xl font-bold text-green-600">R$ {{ number_format($group->budget, 2, ',', '.') }}</span>
                    </div>
                    <div class="p-6 flex flex-col justify-center">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 text-center">Convite</span>
                        <div class="flex items-center gap-2 justify-center">
                            <code class="bg-gray-100 px-3 py-1 rounded text-sm text-gray-600 select-all border border-gray-200">
                                {{ url('/invite/' . $group->invite_token) }}
                            </code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 sm:p-8 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-2 h-full {{ $group->is_drawn ? 'bg-green-500' : 'bg-yellow-400' }}"></div>

                @if(!$group->is_drawn)
                <div class="flex flex-col sm:flex-row justify-between items-center gap-6 ml-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Sorteio Pendente ⏳</h3>
                        <p class="text-gray-500">Aguardando o administrador realizar o sorteio.</p>
                    </div>
                    @if($group->owner_id === auth()->id())
                    <form action="{{ route('groups.draw', $group) }}" method="POST" onsubmit="return confirm('Tem certeza? Isso não pode ser desfeito.')">
                        @csrf
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 flex items-center gap-2">
                            <span>🎲</span> Realizar Sorteio
                        </button>
                    </form>
                    @endif
                </div>
                @else
                <div class="ml-4">
                    <h3 class="text-xl font-bold text-green-700 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Sorteio Realizado!
                    </h3>

                    @if($myPair)
                    <div x-data="{ revealed: false }">
                        <div x-show="!revealed" @click="revealed = true" class="text-center py-12 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border-2 border-dashed border-gray-300 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition group">
                            <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">🕵️</div>
                            <p class="font-bold text-gray-700 text-lg">Quem será o seu amigo secreto?</p>
                            <p class="text-sm text-gray-500 mt-1">Toque aqui para revelar</p>
                        </div>

                        <div x-show="revealed" style="display: none;" class="bg-green-50 p-10 rounded-xl border border-green-200 text-center relative animate-fade-in">
                            <button @click="revealed = false" class="absolute top-4 right-4 text-green-400 hover:text-green-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                            <p class="text-xs text-green-600 uppercase font-bold tracking-widest mb-4">Sua Missão</p>
                            <div class="mb-6">
                                <p class="text-gray-500 text-sm mb-1">Você tirou:</p>
                                <p class="text-4xl sm:text-5xl text-gray-800 font-black tracking-tight">{{ $myPair->giftee->name }}</p>
                            </div>

                            @php $gifteeMember = $group->members->find($myPair->giftee_id); @endphp
                            @if($gifteeMember && $gifteeMember->pivot->wishlist)
                            <div class="bg-white p-4 rounded-lg shadow-sm inline-block max-w-md w-full border-l-4 border-green-400">
                                <p class="text-xs text-gray-400 uppercase font-bold mb-1">Dica de presente:</p>
                                <p class="text-gray-800 italic">"{{ $gifteeMember->pivot->wishlist }}"</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 sm:p-8">
                <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <span class="bg-indigo-100 text-indigo-600 w-6 h-6 rounded-full flex items-center justify-center text-xs">{{ $group->members->count() }}</span>
                    Participantes
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($group->members as $member)
                    <div class="flex items-center p-3 rounded-xl border {{ $member->id === auth()->id() ? 'border-indigo-200 bg-indigo-50' : 'border-gray-100 bg-white' }}">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold shrink-0 shadow-md">
                            {{ substr($member->name, 0, 1) }}
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-800 truncate">
                                {{ $member->name }}
                                @if($member->id === $group->owner_id) 👑 @endif
                            </p>
                            <p class="text-xs text-gray-500 truncate italic">
                                {{ $member->pivot->wishlist ?: 'Sem desejo definido' }}
                            </p>
                        </div>

                        <div class="flex items-center">
                            @if($member->id === auth()->id() && !$group->is_drawn)
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="text-indigo-500 hover:text-indigo-700 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" style="display: none;" class="absolute right-0 mt-2 w-64 bg-white border rounded-xl shadow-xl z-50 p-4">
                                    <form action="{{ route('groups.wishlist.update', $group) }}" method="POST">
                                        @csrf @method('PUT')
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">O que gostaria de ganhar?</label>
                                        <textarea name="wishlist" rows="2" class="w-full text-sm border-gray-300 rounded-lg mb-2 focus:ring-indigo-500 focus:border-indigo-500">{{ $member->pivot->wishlist }}</textarea>
                                        <div class="flex justify-end gap-2">
                                            <button type="submit" class="bg-indigo-600 text-white text-xs px-3 py-1.5 rounded-lg font-bold">Salvar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endif

                            @if($group->owner_id === auth()->id() && $member->id !== auth()->id() && !$group->is_drawn)
                            <form action="{{ route('groups.members.destroy', [$group, $member]) }}" method="POST" onsubmit="return confirm('Remover {{ $member->name }}?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-300 hover:text-red-500 p-1 ml-1 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            @if($group->owner_id === auth()->id())
            <div class="text-center pt-8 pb-4">
                <form action="{{ route('groups.destroy', $group) }}" method="POST" onsubmit="return confirm('TEM CERTEZA ABSOLUTA? Esta ação apagará todo o histórico do grupo.');">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-400 hover:text-red-600 text-sm underline hover:no-underline transition">
                        Excluir este grupo permanentemente
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
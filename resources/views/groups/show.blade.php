<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ $group->name }}
            </h2>
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-indigo-600 flex items-center transition">
                &larr; Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-6 border-b border-gray-100">
                    <div class="flex justify-between items-start">
                        <p class="text-gray-700 text-lg leading-relaxed flex-1 pr-4">{{ $group->description }}</p>
                        @if($group->owner_id === auth()->id())
                        <a href="{{ route('groups.edit', $group) }}" class="p-2 bg-white rounded-lg shadow-sm text-gray-400 hover:text-indigo-600 transition shrink-0" title="Editar Informações">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                    <div class="p-4 sm:p-6 text-center">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Data da Revelação</span>
                        <span class="text-lg sm:text-xl font-bold text-gray-800">{{ $group->event_date->format('d/m/Y') }}</span>
                    </div>
                    <div class="p-4 sm:p-6 text-center">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Valor Máximo</span>
                        <span class="text-lg sm:text-xl font-bold text-green-600">R$ {{ number_format($group->budget, 2, ',', '.') }}</span>
                    </div>
                    <div class="p-4 sm:p-6 flex flex-col justify-center">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 text-center">Link de Convite</span>
                        <div class="flex items-center justify-center">
                            <code class="bg-gray-100 px-3 py-1.5 rounded text-xs sm:text-sm text-gray-600 select-all border border-gray-200 truncate max-w-full">
                                {{ url('/invite/' . $group->invite_token) }}
                            </code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 sm:p-8 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1.5 h-full {{ $group->is_drawn ? 'bg-green-500' : 'bg-yellow-400' }}"></div>

                @if(!$group->is_drawn)
                <div class="flex flex-col sm:flex-row justify-between items-center gap-6 ml-2">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Sorteio Pendente ⏳</h3>
                        <p class="text-gray-500 text-sm sm:text-base">Aguardando o administrador realizar o sorteio.</p>
                    </div>
                    @if($group->owner_id === auth()->id())
                    <form action="{{ route('groups.draw', $group) }}" method="POST" onsubmit="return confirm('Tem certeza? Isso não pode ser desfeito.')" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                            <span>🎲</span> Realizar Sorteio
                        </button>
                    </form>
                    @endif
                </div>
                @else
                <div class="ml-2">
                    <h3 class="text-lg sm:text-xl font-bold text-green-700 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Sorteio Realizado!
                    </h3>

                    @if($myPair)
                    <div x-data="{ revealed: false }" class="mt-2">

                        <div x-show="!revealed"
                            @click="revealed = true"
                            class="text-center py-8 sm:py-10 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border-2 border-dashed border-gray-300 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition group max-w-xl mx-auto">
                            <div class="text-4xl sm:text-5xl mb-3 group-hover:scale-110 transition duration-300 transform">🎁</div>
                            <p class="font-bold text-gray-700 text-base sm:text-lg">Quem será o seu par?</p>
                            <p class="text-xs sm:text-sm text-indigo-500 mt-1 font-medium">Toque aqui para revelar</p>
                        </div>

                        <div x-show="revealed"
                            style="display: none;"
                            @click="revealed = false"
                            class="bg-green-50 p-6 sm:p-8 rounded-xl border border-green-200 text-center relative animate-fade-in cursor-pointer hover:bg-green-100 transition shadow-md max-w-xl mx-auto select-none">

                            <div class="absolute top-3 right-3 sm:top-4 sm:right-4 text-green-400/50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>

                            <p class="inline-block text-[10px] sm:text-xs text-green-600 uppercase font-bold tracking-widest mb-3 bg-white/60 px-2 py-1 rounded-full border border-green-100">
                                Toque em qualquer lugar para esconder 👆
                            </p>

                            <div class="mb-5">
                                <p class="text-gray-500 text-xs sm:text-sm mb-1">A sua missão secreta é presentear:</p>
                                <p class="text-3xl sm:text-4xl text-gray-800 font-black tracking-tight leading-tight mt-1">{{ $myPair->giftee->name }}</p>
                                <p class="text-xs sm:text-sm text-gray-500 font-medium mt-1">{{ $myPair->giftee->email }}</p>
                            </div>

                            @php $gifteeMember = $group->members->find($myPair->giftee_id); @endphp
                            @if($gifteeMember && $gifteeMember->pivot->wishlist)
                            <div class="bg-white p-3 sm:p-4 rounded-lg shadow-sm inline-block w-full text-left border-l-4 border-green-400">
                                <p class="text-[10px] sm:text-xs text-gray-400 uppercase font-bold mb-1 flex items-center gap-1">
                                    🎁 Dica de presente:
                                </p>
                                <p class="text-gray-800 italic text-sm sm:text-base leading-snug">"{{ $gifteeMember->pivot->wishlist }}"</p>
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
                    <span class="bg-indigo-100 text-indigo-600 w-6 h-6 rounded-full flex items-center justify-center text-xs shadow-sm">{{ $group->members->count() }}</span>
                    Participantes
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($group->members as $member)
                    <div class="flex items-center p-3 rounded-xl border {{ $member->id === auth()->id() ? 'border-indigo-200 bg-indigo-50/50' : 'border-gray-100 bg-white' }} transition hover:shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold shrink-0 shadow-md text-sm">
                            {{ substr($member->name, 0, 1) }}
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <div class="flex items-center gap-1">
                                <p class="text-sm font-bold text-gray-800 truncate">
                                    {{ $member->name }}
                                </p>
                                @if($member->id === $group->owner_id)
                                <span class="text-[10px] bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded font-bold" title="Dono do Grupo">👑</span>
                                @endif
                                @if($member->id === auth()->id())
                                <span class="text-[10px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded font-bold">Você</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 truncate italic mt-0.5">
                                {{ $member->pivot->wishlist ?: 'Sem desejo definido' }}
                            </p>
                        </div>

                        <div class="flex items-center pl-2">
                            @if($member->id === auth()->id() && !$group->is_drawn)
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="text-indigo-500 hover:text-indigo-700 p-1.5 rounded-full hover:bg-indigo-100 transition" title="Editar meu desejo">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" style="display: none;" class="absolute right-0 mt-2 w-64 bg-white border rounded-xl shadow-xl z-50 p-4 animate-fade-in-up">
                                    <form action="{{ route('groups.wishlist.update', $group) }}" method="POST">
                                        @csrf @method('PUT')
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">O que gostaria de ganhar?</label>
                                        <textarea name="wishlist" rows="2" class="w-full text-sm border-gray-300 rounded-lg mb-3 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50" placeholder="Ex: Livros, Chocolates...">{{ $member->pivot->wishlist }}</textarea>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="open = false" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1">Cancelar</button>
                                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1.5 rounded-lg font-bold transition shadow-sm">Salvar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endif

                            @if($group->owner_id === auth()->id() && $member->id !== auth()->id() && !$group->is_drawn)
                            <form action="{{ route('groups.members.destroy', [$group, $member]) }}" method="POST" onsubmit="return confirm('Tem a certeza que deseja remover {{ $member->name }} do grupo?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-300 hover:text-red-500 p-1.5 ml-1 rounded-full hover:bg-red-50 transition" title="Remover participante">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
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
            <div class="text-center pt-4 pb-8">
                <form action="{{ route('groups.destroy', $group) }}" method="POST" onsubmit="return confirm('TEM CERTEZA ABSOLUTA? Esta ação apagará todo o histórico do grupo e os sorteios.');">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-400 hover:text-red-600 text-xs sm:text-sm font-medium transition flex items-center justify-center gap-1 mx-auto hover:bg-red-50 px-4 py-2 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Excluir este grupo permanentemente
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
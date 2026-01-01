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

            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 sm:p-8"
                x-data="{
                    refreshMembers() {
                        // Se o sorteio já aconteceu, não precisamos ficar atualizando freneticamente
                        @if($group->is_drawn) return; @endif

                        fetch('{{ route('groups.members.list', $group) }}')
                            .then(response => response.text())
                            .then(html => {
                                // Só atualiza se o HTML mudou para evitar piscar ou fechar modais abertos
                                if (this.$refs.membersContainer.innerHTML !== html) {
                                    // Verificação simples: se o usuário estiver digitando no wishlist, evitamos atualizar agora
                                    // para não fechar o modal na cara dele.
                                    if (document.activeElement.tagName !== 'TEXTAREA' && document.activeElement.tagName !== 'INPUT') {
                                        this.$refs.membersContainer.innerHTML = html;
                                    }
                                }
                            });
                    }
                 }"
                x-init="setInterval(() => refreshMembers(), 10000)" {{-- Atualiza a cada 10 segundos --}}>
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <span class="bg-indigo-100 text-indigo-600 w-6 h-6 rounded-full flex items-center justify-center text-xs shadow-sm">{{ $group->members->count() }}</span>
                        Participantes
                    </h3>

                    @if(!$group->is_drawn)
                    <span class="text-[10px] text-gray-400 bg-gray-50 px-2 py-1 rounded-full animate-pulse">
                        Atualização em tempo real ativa 🟢
                    </span>
                    @endif
                </div>

                <div x-ref="membersContainer">
                    @include('groups.partials.members-list')
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
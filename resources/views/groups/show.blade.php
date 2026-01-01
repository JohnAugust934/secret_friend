<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $group->name }}
            </h2>
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center transition">
                &larr; Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            @if (session('success'))
            <div class="bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-200 p-4 mb-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
            @endif

            @if (session('error'))
            <div class="bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-200 p-4 mb-4" role="alert">
                <p class="font-bold">Ops!</p>
                <p>{{ session('error') }}</p>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-gray-700 dark:to-gray-800 p-6 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between items-start">
                        <p class="text-gray-700 dark:text-gray-300 text-lg leading-relaxed flex-1 pr-4">{{ $group->description }}</p>

                        @if($group->owner_id === auth()->id())
                        <a href="{{ route('groups.edit', $group) }}" class="p-2 bg-white dark:bg-gray-600 rounded-lg shadow-sm text-gray-400 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition shrink-0" title="Editar Informações">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-gray-700">
                    <div class="p-4 sm:p-6 text-center">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Data da Revelação</span>
                        <span class="text-lg sm:text-xl font-bold text-gray-800 dark:text-white">{{ $group->event_date->format('d/m/Y') }}</span>
                    </div>
                    <div class="p-4 sm:p-6 text-center">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Valor Máximo</span>
                        <span class="text-lg sm:text-xl font-bold text-green-600 dark:text-green-400">R$ {{ number_format($group->budget, 2, ',', '.') }}</span>
                    </div>
                    <div class="p-4 sm:p-6 flex flex-col justify-center">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 text-center">Link de Convite</span>
                        <div class="flex items-center justify-center">
                            <code class="bg-gray-100 dark:bg-gray-900 px-3 py-1.5 rounded text-xs sm:text-sm text-gray-600 dark:text-gray-300 select-all border border-gray-200 dark:border-gray-700 truncate max-w-full">
                                {{ route('groups.join', $group->invite_token) }}
                            </code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-6 sm:p-8 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1.5 h-full {{ $group->is_drawn ? 'bg-green-500' : 'bg-indigo-500' }}"></div>

                @if(!$group->is_drawn)
                <div class="ml-2">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 dark:text-white mb-2">Sorteio Pendente</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 text-sm">Aguarde o administrador realizar o sorteio.</p>

                    @if($group->owner_id === auth()->id())
                    <form action="{{ route('groups.draw', $group) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-200 dark:shadow-none transition transform hover:scale-105 flex items-center justify-center gap-2">
                            <span>🎲</span> Realizar Sorteio Agora
                        </button>
                    </form>
                    <p class="text-xs text-gray-400 mt-3">Isso irá gerar os pares e enviar e-mails para todos.</p>
                    @endif
                </div>
                @else
                <div class="ml-2">
                    <h3 class="text-lg sm:text-xl font-bold text-green-700 dark:text-green-400 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Sorteio Realizado!
                    </h3>

                    @if($myPair)
                    {{--
                                CORREÇÃO DO ERRO: 
                                Buscamos o membro sorteado dentro da coleção $group->members, 
                                pois é lá que está a informação do 'pivot' (wishlist).
                            --}}
                    @php
                    $giftee = $group->members->find($myPair->giftee_id);
                    @endphp

                    <div x-data="{ revealed: false }" class="mt-2">
                        <div x-show="!revealed" @click="revealed = true" class="text-center py-8 sm:py-10 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-500 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-500 transition group max-w-xl mx-auto">
                            <div class="text-4xl sm:text-5xl mb-3 group-hover:scale-110 transition duration-300 transform">🎁</div>
                            <p class="font-bold text-gray-700 dark:text-gray-200 text-base sm:text-lg">Quem será o seu par?</p>
                            <p class="text-xs sm:text-sm text-indigo-500 dark:text-indigo-300 mt-1 font-medium">Toque aqui para revelar</p>
                        </div>

                        <div x-show="revealed" style="display: none;" @click="revealed = false" class="bg-green-50 dark:bg-green-900/30 p-6 sm:p-8 rounded-xl border border-green-200 dark:border-green-800 text-center relative animate-fade-in cursor-pointer hover:bg-green-100 dark:hover:bg-green-900/50 transition shadow-md max-w-xl mx-auto select-none">
                            <div class="absolute top-3 right-3 sm:top-4 sm:right-4 text-green-400/50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>

                            <p class="inline-block text-[10px] sm:text-xs text-green-600 dark:text-green-300 uppercase font-bold tracking-widest mb-3 bg-white dark:bg-gray-800/60 px-2 py-1 rounded-full border border-green-100 dark:border-green-800">
                                Toque em qualquer lugar para esconder 👆
                            </p>

                            <div class="mb-5">
                                <p class="text-gray-500 dark:text-gray-400 text-xs sm:text-sm mb-1">A sua missão secreta é presentear:</p>
                                {{-- Usamos a variável $giftee encontrada acima --}}
                                <p class="text-3xl sm:text-4xl text-gray-800 dark:text-white font-black tracking-tight leading-tight mt-1">{{ $giftee->name }}</p>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-medium mt-1">{{ $giftee->email }}</p>
                            </div>

                            @if($giftee->pivot->wishlist)
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-left border border-green-100 dark:border-green-800 shadow-sm relative">
                                <span class="absolute -top-2 left-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300 text-[10px] font-bold px-2 py-0.5 rounded uppercase">Desejos</span>
                                <p class="text-gray-600 dark:text-gray-300 text-sm italic mt-1">"{{ $giftee->pivot->wishlist }}"</p>
                            </div>
                            @else
                            <p class="text-gray-400 dark:text-gray-500 text-xs italic">Esta pessoa ainda não definiu lista de desejos.</p>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="text-center py-8 bg-gray-50 dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600">
                        <p class="text-gray-500 dark:text-gray-300">Você não participou deste sorteio.</p>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-6 sm:p-8"
                x-data="{
                    refreshMembers() {
                        @if($group->is_drawn) return; @endif
                        fetch('{{ route('groups.members.list', $group) }}')
                            .then(response => response.text())
                            .then(html => {
                                if (this.$refs.membersContainer.innerHTML !== html) {
                                    if (document.activeElement.tagName !== 'TEXTAREA' && document.activeElement.tagName !== 'INPUT') {
                                        this.$refs.membersContainer.innerHTML = html;
                                    }
                                }
                            });
                    }
                 }"
                x-init="setInterval(() => refreshMembers(), 10000)">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <span class="bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 w-6 h-6 rounded-full flex items-center justify-center text-xs shadow-sm">{{ $group->members->count() }}</span>
                        Participantes
                    </h3>
                    @if(!$group->is_drawn)
                    <span class="text-[10px] text-gray-400 bg-gray-50 dark:bg-gray-700 px-2 py-1 rounded-full animate-pulse">
                        Atualização em tempo real ativa 🟢
                    </span>
                    @endif
                </div>

                <div x-ref="membersContainer">
                    @include('groups.partials.members-list')
                </div>
            </div>

            @if($group->owner_id === auth()->id() && !$group->is_drawn)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-6 sm:p-8">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2 flex items-center gap-2">
                    🛡️ Restrições de Sorteio
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Defina quem <strong>não pode</strong> tirar quem (ex: casais, irmãos).</p>

                <form action="{{ route('groups.exclusions.store', $group) }}" method="POST" class="flex flex-col sm:flex-row gap-4 mb-8 bg-gray-50 dark:bg-gray-700 p-4 rounded-xl border border-gray-100 dark:border-gray-600">
                    @csrf
                    <div class="flex-1">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Quem tira</label>
                        <select name="user_id" class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach($group->members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center justify-center pt-6">
                        <span class="text-red-400 font-bold">🚫 NÃO TIRA ➡️</span>
                    </div>
                    <div class="flex-1">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Quem recebe</label>
                        <select name="excluded_id" class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach($group->members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full sm:w-auto bg-gray-800 hover:bg-black dark:bg-gray-600 dark:hover:bg-gray-500 text-white font-bold py-2.5 px-4 rounded-lg shadow transition text-sm">
                            Adicionar
                        </button>
                    </div>
                </form>

                @if($group->exclusions->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($group->exclusions as $exclusion)
                    <div class="flex justify-between items-center bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 p-3 rounded-lg">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-bold">{{ $exclusion->participant->name }}</span>
                            <span class="text-red-400 mx-1">⊘</span>
                            <span>{{ $exclusion->excluded->name }}</span>
                        </div>
                        <form action="{{ route('groups.exclusions.destroy', [$group, $exclusion]) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-700 dark:hover:text-red-300 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-400 italic text-center">Nenhuma restrição definida.</p>
                @endif
            </div>
            @endif

            <div class="text-center pt-4 pb-8">
                @if($group->owner_id === auth()->id())
                <form action="{{ route('groups.destroy', $group) }}" method="POST" onsubmit="return confirm('TEM CERTEZA ABSOLUTA? Esta ação apagará todo o histórico do grupo e os sorteios.');">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-400 hover:text-red-600 text-xs sm:text-sm font-medium transition flex items-center justify-center gap-1 mx-auto hover:bg-red-50 dark:hover:bg-red-900/20 px-4 py-2 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Excluir este grupo permanentemente
                    </button>
                </form>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
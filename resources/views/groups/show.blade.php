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

    <div class="py-8 pb-24 sm:pb-8"
        x-data="{ 
            activeTab: 'draw',
            showDrawAnimation: false,
            drawMessages: ['Embaralhando nomes...', 'Verificando restrições...', 'Sorteando...', 'Fechando os envelopes...'],
            currentMessage: 'Iniciando...',
            startDraw() {
                this.showDrawAnimation = true;
                let step = 0;
                // Troca a mensagem a cada 600ms para criar suspense
                const interval = setInterval(() => {
                    this.currentMessage = this.drawMessages[step % this.drawMessages.length];
                    step++;
                }, 600);

                // Envia o formulário após 3 segundos de animação
                setTimeout(() => {
                    clearInterval(interval);
                    this.$refs.drawForm.submit();
                }, 3000); 
            }
         }">

        <div x-show="showDrawAnimation"
            style="display: none;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-indigo-900/90 backdrop-blur-md text-white">

            <div class="relative mb-8">
                <div class="absolute inset-0 bg-white/20 rounded-full animate-ping"></div>
                <div class="relative bg-white text-indigo-600 w-32 h-32 rounded-full flex items-center justify-center text-6xl shadow-2xl animate-bounce">
                    🎲
                </div>
            </div>

            <h2 class="text-3xl font-black tracking-tight mb-2 animate-pulse" x-text="currentMessage">
                Sorteando...
            </h2>
            <p class="text-indigo-200 text-sm">Por favor, aguarde...</p>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="flex space-x-1 rounded-xl bg-gray-200/50 dark:bg-gray-700/50 p-1">
                <button @click="activeTab = 'draw'"
                    :class="activeTab === 'draw' ? 'bg-white dark:bg-gray-800 shadow text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:bg-white/[0.12] hover:text-gray-700'"
                    class="w-full rounded-lg py-2.5 text-sm font-bold leading-5 ring-white ring-opacity-60 ring-offset-2 ring-offset-indigo-400 focus:outline-none focus:ring-2 transition">
                    Sorteio
                </button>

                <button @click="activeTab = 'members'"
                    :class="activeTab === 'members' ? 'bg-white dark:bg-gray-800 shadow text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:bg-white/[0.12] hover:text-gray-700'"
                    class="w-full rounded-lg py-2.5 text-sm font-bold leading-5 ring-white ring-opacity-60 ring-offset-2 ring-offset-indigo-400 focus:outline-none focus:ring-2 transition flex items-center justify-center gap-2">
                    Participantes
                    <span class="bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 py-0.5 px-2 rounded-full text-xs">
                        {{ $group->members->count() }}
                    </span>
                </button>

                @if($group->owner_id === auth()->id())
                <button @click="activeTab = 'settings'"
                    :class="activeTab === 'settings' ? 'bg-white dark:bg-gray-800 shadow text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:bg-white/[0.12] hover:text-gray-700'"
                    class="w-full rounded-lg py-2.5 text-sm font-bold leading-5 ring-white ring-opacity-60 ring-offset-2 ring-offset-indigo-400 focus:outline-none focus:ring-2 transition">
                    Admin
                </button>
                @endif
            </div>

            <div x-show="activeTab === 'draw'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="space-y-8">

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                        <div class="flex-1">
                            <p class="text-gray-500 dark:text-gray-400 text-sm uppercase tracking-wider font-bold mb-1">Descrição</p>
                            <p class="text-gray-700 dark:text-gray-300">{{ $group->description }}</p>
                        </div>
                        <div class="flex gap-8">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm uppercase tracking-wider font-bold">Data</p>
                                <p class="text-xl font-bold text-gray-800 dark:text-white">{{ $group->event_date->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm uppercase tracking-wider font-bold">Orçamento</p>
                                <p class="text-xl font-bold text-green-600 dark:text-green-400">R$ {{ number_format($group->budget, 2, ',', '.') }}</p>
                            </div>
                        </div>

                        @if(!$group->is_drawn && $group->owner_id === auth()->id())
                        <form action="{{ route('groups.draw', $group) }}" method="POST" x-ref="drawForm" @submit.prevent="startDraw()">
                            @csrf
                            <button type="submit"
                                class="relative w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition transform hover:scale-105 flex items-center justify-center gap-2">
                                <span class="flex items-center gap-2"><span>🎲</span> Realizar Sorteio</span>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <div class="relative min-h-[400px] flex justify-center perspective-1000">
                    @if(!$group->is_drawn)
                    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-3xl shadow-xl border-2 border-dashed border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center p-8 text-center">
                        <div class="bg-indigo-50 dark:bg-indigo-900/50 p-6 rounded-full mb-4 animate-pulse">
                            <span class="text-4xl">⏳</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white">Aguardando Sorteio</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-2">O administrador ainda não realizou o sorteio. Fique atento!</p>
                        <div class="mt-6">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                Pendente
                            </span>
                        </div>
                    </div>
                    @elseif($myPair)
                    @php
                    $giftee = $group->members->find($myPair->giftee_id);
                    @endphp

                    <div x-data="{ flipped: false }" class="relative w-full max-w-sm h-96 group [perspective:1000px]">
                        <div class="relative w-full h-full transition-all duration-700 [transform-style:preserve-3d]"
                            :class="{ '[transform:rotateY(180deg)]': flipped }">

                            <div class="absolute inset-0 w-full h-full bg-gradient-to-br from-indigo-600 to-purple-700 rounded-3xl shadow-2xl flex flex-col items-center justify-center text-white cursor-pointer [backface-visibility:hidden]"
                                @click="
                                        flipped = true;
                                        confetti({
                                            particleCount: 150,
                                            spread: 70,
                                            origin: { y: 0.6 },
                                            colors: ['#ffffff', '#fbbf24', '#a5b4fc']
                                        });
                                    ">

                                <div class="bg-white/20 p-6 rounded-full mb-6 backdrop-blur-sm animate-bounce">
                                    <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                                    </svg>
                                </div>

                                <h3 class="text-2xl font-black tracking-wider mb-2">SEU PAR É...</h3>
                                <p class="text-indigo-200 text-sm font-medium animate-pulse">Toque para revelar</p>
                            </div>

                            <div class="absolute inset-0 w-full h-full bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 flex flex-col items-center justify-center p-6 text-center [transform:rotateY(180deg)] [backface-visibility:hidden]">

                                <div class="absolute top-4 right-4">
                                    <button @click="flipped = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="w-20 h-20 bg-gradient-to-r from-green-400 to-emerald-600 rounded-full flex items-center justify-center text-3xl font-bold text-white mb-4 shadow-lg">
                                    {{ substr($giftee->name, 0, 1) }}
                                </div>

                                <h3 class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Você tirou</h3>
                                <h2 class="text-2xl sm:text-3xl font-black text-gray-800 dark:text-white mb-1 leading-tight">
                                    {{ $giftee->name }}
                                </h2>
                                <p class="text-gray-400 text-sm mb-6">{{ $giftee->email }}</p>

                                <div class="w-full bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-100 dark:border-yellow-800/30 rounded-xl p-4 text-left relative">
                                    <span class="absolute -top-2.5 left-3 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-300 text-[10px] font-bold px-2 py-0.5 rounded">
                                        🎁 DICA DE PRESENTE
                                    </span>
                                    <p class="text-gray-700 dark:text-gray-300 text-sm mt-1 italic leading-relaxed">
                                        "{{ $giftee->pivot->wishlist ?: 'Sem preferências definidas...' }}"
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-12">
                        <p class="text-gray-500">Você não está participando deste sorteio.</p>
                    </div>
                    @endif
                </div>
            </div>

            <div x-show="activeTab === 'members'"
                style="display: none;"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6"
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
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                        Quem está no grupo?
                    </h3>

                    @if(!$group->is_drawn)
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500 hidden sm:inline">Convide:</span>
                        <code class="bg-gray-100 dark:bg-gray-900 px-2 py-1 rounded text-xs text-indigo-600 dark:text-indigo-400 border border-gray-200 dark:border-gray-700 select-all cursor-pointer" onclick="navigator.clipboard.writeText('{{ route('groups.join', $group->invite_token) }}'); alert('Link copiado!')">
                            {{ route('groups.join', $group->invite_token) }}
                        </code>
                    </div>
                    @endif
                </div>

                <div x-ref="membersContainer">
                    @include('groups.partials.members-list')
                </div>
            </div>

            @if($group->owner_id === auth()->id() && !$group->is_drawn)
            <div x-show="activeTab === 'settings'"
                style="display: none;"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">

                <h3 class="font-bold text-gray-700 dark:text-gray-300 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Configurações do Grupo
                </h3>

                <div class="mb-8">
                    <h4 class="text-sm font-bold text-gray-500 uppercase mb-3">Restrições (Quem não tira quem)</h4>
                    <form action="{{ route('groups.exclusions.store', $group) }}" method="POST" class="flex flex-col sm:flex-row gap-2 mb-4">
                        @csrf
                        <div class="flex-1">
                            <select name="user_id" class="w-full rounded-lg border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Quem tira...</option>
                                @foreach($group->members as $m) <option value="{{ $m->id }}">{{ $m->name }}</option> @endforeach
                            </select>
                        </div>
                        <span class="hidden sm:flex items-center text-gray-400">🚫</span>
                        <div class="flex-1">
                            <select name="excluded_id" class="w-full rounded-lg border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Não pode tirar...</option>
                                @foreach($group->members as $m) <option value="{{ $m->id }}">{{ $m->name }}</option> @endforeach
                            </select>
                        </div>
                        <button type="submit"
                            x-data="{ loading: false }"
                            @click="loading = true; setTimeout(() => loading = false, 5000)"
                            class="relative bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-black transition flex items-center justify-center min-w-[100px]">
                            <span x-show="!loading">Adicionar</span>
                            <span x-show="loading" style="display: none;"><svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg></span>
                        </button>
                    </form>

                    @if($group->exclusions->count() > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($group->exclusions as $ex)
                        <span class="inline-flex items-center gap-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 px-3 py-1.5 rounded-lg text-xs shadow-sm text-gray-700 dark:text-gray-200">
                            <span class="font-bold">{{ $ex->participant->name }}</span>
                            <span class="text-red-400 mx-1">não tira</span>
                            <span class="font-bold">{{ $ex->excluded->name }}</span>
                            <form action="{{ route('groups.exclusions.destroy', [$group, $ex]) }}" method="POST" class="ml-2 border-l border-gray-200 dark:border-gray-600 pl-2">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    x-data="{ loading: false }"
                                    @click="loading = true"
                                    class="text-gray-400 hover:text-red-500 font-bold text-lg leading-none flex items-center justify-center w-4 h-4">
                                    <span x-show="!loading">&times;</span>
                                    <span x-show="loading" style="display: none;"><svg class="animate-spin h-3 w-3 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg></span>
                                </button>
                            </form>
                        </span>
                        @endforeach
                    </div>
                    @else
                    <p class="text-xs text-gray-400 italic">Nenhuma restrição definida.</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <a href="{{ route('groups.edit', $group) }}" class="flex items-center justify-center gap-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:border-indigo-300 dark:hover:border-indigo-500 text-gray-700 dark:text-white font-bold py-3 rounded-xl transition">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar Detalhes
                    </a>

                    <form action="{{ route('groups.destroy', $group) }}" method="POST" onsubmit="return confirm('ATENÇÃO: Isso apagará o grupo e todo o histórico de sorteio para sempre. Tem certeza?');">
                        @csrf @method('DELETE')
                        <button type="submit"
                            x-data="{ loading: false }"
                            @click="if(confirm('Tem certeza absoluta?')) { loading = true } else { $event.preventDefault() }"
                            class="relative w-full flex items-center justify-center gap-2 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 font-bold py-3 rounded-xl transition">
                            <span x-show="!loading" class="flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg> Excluir Grupo</span>
                            <span x-show="loading" style="display: none;"><svg class="animate-spin h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg></span>
                        </button>
                    </form>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
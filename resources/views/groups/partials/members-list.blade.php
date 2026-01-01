<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($group->members as $member)
    <div class="flex items-center p-3 rounded-xl border {{ $member->id === auth()->id() ? 'border-indigo-200 bg-indigo-50/50 dark:border-indigo-700 dark:bg-indigo-900/30' : 'border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800' }} transition hover:shadow-sm">

        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold shrink-0 shadow-md text-sm">
            {{ substr($member->name, 0, 1) }}
        </div>

        <div class="ml-3 flex-1 min-w-0">
            <div class="flex items-center gap-1">
                <p class="text-sm font-bold text-gray-800 dark:text-gray-200 truncate">
                    {{ $member->id === auth()->id() ? 'Você' : $member->name }}
                </p>
                @if($member->id === $group->owner_id)
                <span class="text-[10px] bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-400 px-1.5 py-0.5 rounded font-bold" title="Dono do Grupo">👑</span>
                @endif
                @if($member->id === auth()->id())
                <span class="text-[10px] bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 px-1.5 py-0.5 rounded font-bold">Você</span>
                @endif
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate italic mt-0.5">
                {{ $member->pivot->wishlist ? '"' . $member->pivot->wishlist . '"' : 'Sem desejo definido' }}
            </p>
        </div>

        <div class="flex items-center pl-2">
            {{-- Botão Remover (Apenas Dono pode remover outros, e não a si mesmo) --}}
            @if($group->owner_id === auth()->id() && $member->id !== auth()->id() && !$group->is_drawn)
            <form action="{{ route('groups.members.destroy', [$group, $member]) }}" method="POST" onsubmit="return confirm('Remover este participante?');">
                @csrf @method('DELETE')
                <button type="submit"
                    x-data="{ loading: false }"
                    @click="if(confirm('Tem certeza?')) { loading = true } else { $event.preventDefault() }"
                    class="text-gray-300 hover:text-red-500 transition p-1 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20" title="Remover do grupo">
                    <span x-show="!loading">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </span>
                    <span x-show="loading" style="display: none;">
                        <svg class="animate-spin h-4 w-4 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </form>
            @endif

            {{-- Botão Editar Wishlist (Apenas o próprio usuário) --}}
            @if($member->id === auth()->id() && !$group->is_drawn)
            <button @click="$dispatch('open-modal', 'edit-wishlist')" class="text-gray-300 hover:text-indigo-500 transition p-1 rounded-full hover:bg-indigo-50 dark:hover:bg-indigo-900/20" title="Editar Desejo">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
            </button>
            @endif
        </div>
    </div>
    @endforeach

    <x-modal name="edit-wishlist" focusable>
        <form method="post" action="{{ route('groups.wishlist.update', $group) }}" class="p-6">
            @csrf
            @method('PUT') {{-- <--- CORREÇÃO AQUI (Era PATCH, agora é PUT para combinar com a rota) --}}

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Atualizar Lista de Desejos</h2>

            <div class="mt-4">
                <x-input-label for="wishlist" value="O que você gostaria de ganhar?" />
                <textarea id="wishlist" name="wishlist" class="w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm h-32 p-3" placeholder="Ex: Livros de ficção, Camiseta M, Chocolate...">{{ $group->members->find(auth()->id())->pivot->wishlist }}</textarea>
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button class="ml-3">Salvar</x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($group->members as $member)
    <div class="flex items-center p-3 rounded-xl border {{ $member->id === auth()->id() ? 'border-indigo-200 bg-indigo-50/50 dark:border-indigo-700 dark:bg-indigo-900/30' : 'border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800' }} transition hover:shadow-sm">
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold shrink-0 shadow-md text-sm">
            {{ substr($member->name, 0, 1) }}
        </div>
        <div class="ml-3 flex-1 min-w-0">
            <div class="flex items-center gap-1">
                <p class="text-sm font-bold text-gray-800 dark:text-gray-200 truncate">
                    {{ $member->name }}
                </p>
                @if($member->id === $group->owner_id)
                <span class="text-[10px] bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-400 px-1.5 py-0.5 rounded font-bold" title="Dono do Grupo">👑</span>
                @endif
                @if($member->id === auth()->id())
                <span class="text-[10px] bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 px-1.5 py-0.5 rounded font-bold">Você</span>
                @endif
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate italic mt-0.5">
                {{ $member->pivot->wishlist ?: 'Sem desejo definido' }}
            </p>
        </div>

        <div class="flex items-center pl-2">
            @if($member->id === auth()->id() && !$group->is_drawn)
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 p-1.5 rounded-full hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition" title="Editar meu desejo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                </button>

                <div x-show="open" @click.away="open = false" style="display: none;" class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-xl shadow-xl z-50 p-4 animate-fade-in-up">
                    <form action="{{ route('groups.wishlist.update', $group) }}" method="POST">
                        @csrf @method('PUT')
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">O que gostaria de ganhar?</label>
                        <textarea name="wishlist" rows="2" class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg mb-3 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200" placeholder="Ex: Livros, Chocolates...">{{ $member->pivot->wishlist }}</textarea>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="open = false" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 px-2 py-1">Cancelar</button>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1.5 rounded-lg font-bold transition shadow-sm">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            @if($group->owner_id === auth()->id() && $member->id !== auth()->id() && !$group->is_drawn)
            <form action="{{ route('groups.members.destroy', [$group, $member]) }}" method="POST" onsubmit="return confirm('Tem a certeza que deseja remover {{ $member->name }} do grupo?');">
                @csrf @method('DELETE')
                <button type="submit" class="text-gray-300 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400 p-1.5 ml-1 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Remover participante">
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
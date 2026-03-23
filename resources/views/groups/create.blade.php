<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Criar Novo Grupo') }}
        </h2>
    </x-slot>

    <div class="py-8"
        x-data="{
            draftKey: 'group-create-draft-v1',
            hasOldInput: {{ old('name') || old('description') || old('event_date') || old('budget') || old('wishlist') ? 'true' : 'false' }},
            name: @js(old('name', '')),
            description: @js(old('description', '')),
            event_date: @js(old('event_date', '')),
            budget: @js(old('budget', '')),
            wishlist: @js(old('wishlist', '')),
            initDraft() {
                if (this.hasOldInput) return;
                const raw = localStorage.getItem(this.draftKey);
                if (!raw) return;

                try {
                    const data = JSON.parse(raw);
                    this.name = data.name ?? this.name;
                    this.description = data.description ?? this.description;
                    this.event_date = data.event_date ?? this.event_date;
                    this.budget = data.budget ?? this.budget;
                    this.wishlist = data.wishlist ?? this.wishlist;
                } catch (e) {
                    localStorage.removeItem(this.draftKey);
                }
            },
            saveDraft() {
                localStorage.setItem(this.draftKey, JSON.stringify({
                    name: this.name,
                    description: this.description,
                    event_date: this.event_date,
                    budget: this.budget,
                    wishlist: this.wishlist,
                }));
            },
            clearDraft() {
                localStorage.removeItem(this.draftKey);
            }
        }"
        x-init="initDraft()">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 sm:p-8">
                    <form action="{{ route('groups.store') }}" method="POST" class="space-y-6" @submit="clearDraft()">
                        @csrf

                        <div>
                            <label for="name" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Nome do Grupo</label>
                            <input type="text" name="name" id="name" required
                                x-model="name"
                                @input.debounce.300ms="saveDraft()"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                placeholder="Ex: Natal da Familia 2025">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Descricao</label>
                            <textarea name="description" id="description" rows="3"
                                x-model="description"
                                @input.debounce.300ms="saveDraft()"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                placeholder="Regras, local da troca de presentes, etc..."></textarea>
                            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="event_date" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Data da Revelacao</label>
                                <input type="date" name="event_date" id="event_date" required
                                    x-model="event_date"
                                    @change="saveDraft()"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                @error('event_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="budget" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Valor Maximo (R$)</label>
                                <input type="number" step="0.01" name="budget" id="budget" required
                                    x-model="budget"
                                    @input.debounce.300ms="saveDraft()"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                    placeholder="0,00">
                                @error('budget') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                            <label for="wishlist" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Sua Lista de Desejos (Opcional)</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Ajude seu amigo secreto a escolher seu presente.</p>
                            <textarea name="wishlist" id="wishlist" rows="2"
                                x-model="wishlist"
                                @input.debounce.300ms="saveDraft()"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                placeholder="Ex: Livros, Camiseta M, Chocolates..."></textarea>
                        </div>

                        <div class="flex items-center justify-between pt-4">
                            <span class="text-xs text-gray-400">Rascunho salvo automaticamente.</span>
                            <div class="flex items-center">
                                <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mr-4 transition">Cancelar</a>
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-200 dark:shadow-none transition transform hover:scale-105">
                                    Criar Grupo
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

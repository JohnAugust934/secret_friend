<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Criar Novo Grupo') }}
        </h2>
    </x-slot>

    <div class="py-8"
        x-data="{
            draftKey: 'group-create-draft-v2',
            hasOldInput: {{ old('name') || old('description') || old('event_date') || old('budget') || old('budget_limit') || old('location') || old('wishlist') ? 'true' : 'false' }},
            name: @js(old('name', '')),
            description: @js(old('description', '')),
            event_date: @js(old('event_date', '')),
            budget: @js(old('budget', '')),
            budget_limit: @js(old('budget_limit', '')),
            location: @js(old('location', '')),
            wishlist: @js(old('wishlist', '')),
            initDraft() {
                if (this.hasOldInput) return;
                const raw = localStorage.getItem(this.draftKey);
                if (!raw) return;

                try {
                    const data = JSON.parse(raw);
                    this.name         = data.name         ?? this.name;
                    this.description  = data.description  ?? this.description;
                    this.event_date   = data.event_date   ?? this.event_date;
                    this.budget       = data.budget       ?? this.budget;
                    this.budget_limit = data.budget_limit ?? this.budget_limit;
                    this.location     = data.location     ?? this.location;
                    this.wishlist     = data.wishlist      ?? this.wishlist;
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
                    budget_limit: this.budget_limit,
                    location: this.location,
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

                        {{-- Nome --}}
                        <div>
                            <label for="name" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Nome do Grupo</label>
                            <input type="text" name="name" id="name" required
                                x-model="name"
                                @input.debounce.300ms="saveDraft()"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                placeholder="Ex: Natal da Família 2026">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Descrição --}}
                        <div>
                            <label for="description" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Descrição</label>
                            <textarea name="description" id="description" rows="3"
                                x-model="description"
                                @input.debounce.300ms="saveDraft()"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                placeholder="Regras, informações gerais..."></textarea>
                            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Data + Local --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="event_date" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Data da Revelação</label>
                                <input type="date" name="event_date" id="event_date" required
                                    x-model="event_date"
                                    @change="saveDraft()"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                @error('event_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="location" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    Local do Evento
                                    <span class="font-normal text-gray-400">(opcional)</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </span>
                                    <input type="text" name="location" id="location"
                                        x-model="location"
                                        @input.debounce.300ms="saveDraft()"
                                        class="w-full pl-9 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                        placeholder="Ex: Casa da Avó">
                                </div>
                                @error('location') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Orçamento --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="budget" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    Valor Máximo (R$)
                                    <span class="font-normal text-gray-400">(opcional)</span>
                                </label>
                                <input type="number" step="0.01" name="budget" id="budget"
                                    x-model="budget"
                                    @input.debounce.300ms="saveDraft()"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                    placeholder="0,00">
                                @error('budget') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="budget_limit" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    Intervalo de Orçamento
                                    <span class="font-normal text-gray-400">(opcional)</span>
                                </label>
                                <input type="text" name="budget_limit" id="budget_limit"
                                    x-model="budget_limit"
                                    @input.debounce.300ms="saveDraft()"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                    placeholder="Ex: 10€ - 20€">
                                <p class="text-xs text-gray-400 mt-1">Descrição livre do intervalo de preço.</p>
                                @error('budget_limit') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Wishlist --}}
                        <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                            <label for="wishlist" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Sua Lista de Desejos (Opcional)</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Ajude o seu amigo secreto a escolher o presente.</p>
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

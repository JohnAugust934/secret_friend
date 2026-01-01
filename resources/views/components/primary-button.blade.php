{{-- Adicionamos suporte a wire:loading (se usar Livewire futuramente) e x-data para forms padrão --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'relative inline-flex items-center justify-center px-6 py-3 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 dark:hover:bg-indigo-400 focus:bg-indigo-700 dark:focus:bg-indigo-400 active:bg-indigo-900 dark:active:bg-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 transform active:scale-95 shadow-lg shadow-indigo-500/30 disabled:opacity-70 disabled:cursor-not-allowed']) }}
    x-data="{ loading: false }"
    @click="if($el.type === 'submit') { loading = true; setTimeout(() => loading = false, 10000); }" {{-- Reset de segurança após 10s --}}>
    <span x-show="!loading" class="flex items-center gap-2">
        {{ $slot }}
    </span>

    <span x-show="loading" style="display: none;" class="absolute inset-0 flex items-center justify-center">
        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </span>
</button>
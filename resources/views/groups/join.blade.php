<x-guest-layout>
    <div class="text-center">
        <div class="bg-indigo-50 dark:bg-indigo-900/30 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-indigo-500 dark:text-indigo-400">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
        </div>

        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-2">Convite Especial!</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6">Você foi convidado para participar do Amigo Secreto:</p>

        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-xl p-4 mb-8">
            <h3 class="text-xl font-bold text-indigo-900 dark:text-indigo-300">{{ $group->name }}</h3>
            <p class="text-sm text-indigo-700 dark:text-indigo-400 mt-1">{{ $group->description }}</p>
            <div class="mt-3 flex justify-center gap-4 text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide">
                <span>🗓 {{ $group->event_date->format('d/m/Y') }}</span>
                <span>💰 R$ {{ number_format($group->budget, 2, ',', '.') }}</span>
            </div>
        </div>

        <form action="{{ route('groups.join.store', $group->invite_token) }}" method="POST">
            @csrf

            <div class="text-left mb-6">
                <label for="wishlist" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">O que você gostaria de ganhar?</label>
                <textarea name="wishlist" id="wishlist" rows="3"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                    placeholder="Ex: Livros, Utensílios de cozinha..."></textarea>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-200 dark:shadow-none transition transform hover:scale-105">
                Confirmar Presença
            </button>
        </form>
    </div>
</x-guest-layout>
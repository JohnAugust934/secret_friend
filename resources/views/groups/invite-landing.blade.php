<x-guest-layout>
    <div class="text-center">
        <div class="bg-indigo-50 dark:bg-indigo-900/30 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-indigo-500 dark:text-indigo-400">
            <span class="text-2xl">🎉</span>
        </div>

        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-2">Voce recebeu um convite</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6">Entre ou crie sua conta para participar deste grupo:</p>

        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-xl p-4 mb-8">
            <h3 class="text-xl font-bold text-indigo-900 dark:text-indigo-300">{{ $group->name }}</h3>
            <p class="text-sm text-indigo-700 dark:text-indigo-400 mt-1">{{ $group->description }}</p>
            <div class="mt-3 flex justify-center gap-4 text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide">
                <span>📅 {{ $group->event_date->format('d/m/Y') }}</span>
                <span>💰 R$ {{ number_format($group->budget, 2, ',', '.') }}</span>
            </div>
        </div>

        <div class="space-y-3">
            <a href="{{ route('login', ['invite_token' => $group->invite_token]) }}"
                class="w-full inline-flex justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-200 dark:shadow-none transition transform hover:scale-105">
                Entrar para aceitar convite
            </a>
            <a href="{{ route('register', ['invite_token' => $group->invite_token]) }}"
                class="w-full inline-flex justify-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-100 font-bold py-3 px-6 rounded-xl transition transform hover:scale-105">
                Criar conta e participar
            </a>
        </div>
    </div>
</x-guest-layout>

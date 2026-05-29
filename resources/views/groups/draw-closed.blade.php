<x-guest-layout>
    <div class="text-center">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-700 p-8 rounded-t-3xl -mx-8 -mt-8 text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4">
                <span class="text-3xl">🎁</span>
            </div>
            <h1 class="text-2xl font-black text-white mb-1">Sorteio já Realizado!</h1>
            <p class="text-indigo-200 text-sm">Este grupo já escolheu os seus pares.</p>
        </div>

        <div class="px-2 pb-2 space-y-4">
            <p class="text-gray-700 dark:text-gray-300 text-sm">
                O grupo <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $group->name }}</span>
                já realizou o sorteio. O link de convite não aceita mais novos participantes.
            </p>

            @if($group->event_date)
            <div class="inline-flex items-center gap-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg px-4 py-2 text-sm font-medium">
                📅 {{ $group->event_date->format('d/m/Y') }}
            </div>
            @endif

            <p class="text-gray-500 dark:text-gray-400 text-sm mt-4">
                Se já és membro deste grupo, acede ao teu painel para ver o resultado.
            </p>

            <div class="flex flex-col gap-3 pt-4">
                @auth
                <a href="{{ route('dashboard') }}"
                   class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl transition text-sm text-center shadow-lg shadow-indigo-200 dark:shadow-none">
                    Ir para o Painel
                </a>
                @else
                <a href="{{ route('login') }}"
                   class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl transition text-sm text-center shadow-lg shadow-indigo-200 dark:shadow-none">
                    Fazer Login
                </a>
                @endauth

                <a href="{{ url('/') }}"
                   class="w-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold py-3 px-4 rounded-xl transition text-sm text-center">
                    Página Inicial
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>

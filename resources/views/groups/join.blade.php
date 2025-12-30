<x-guest-layout>
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full mb-4 shadow-inner">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
        </div>
        <h2 class="text-3xl font-black text-gray-800 tracking-tight">Você foi convidado!</h2>
        <p class="text-gray-500 mt-2">Para participar do Amigo Secreto:</p>
        <div class="mt-4 bg-indigo-50 py-3 px-6 rounded-lg inline-block border border-indigo-100">
            <span class="text-xl font-bold text-indigo-700">{{ $group->name }}</span>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-8 text-center text-sm">
        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
            <span class="block text-gray-400 font-bold uppercase text-xs">Revelação</span>
            <span class="font-semibold text-gray-700">{{ $group->event_date->format('d/m/Y') }}</span>
        </div>
        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
            <span class="block text-gray-400 font-bold uppercase text-xs">Valor</span>
            <span class="font-semibold text-green-600">R$ {{ number_format($group->budget, 2, ',', '.') }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('groups.join.store', $group->invite_token) }}">
        @csrf

        <div class="mb-4 text-left">
            <x-input-label value="Participando como:" class="text-gray-600 font-semibold" />
            <div class="flex items-center gap-3 mt-2 p-3 bg-gray-50 rounded-xl border border-gray-200">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <span class="font-medium text-gray-800">{{ Auth::user()->name }}</span>
            </div>
        </div>

        <div class="text-left">
            <x-input-label for="wishlist" value="Sua lista de desejos (Dica de presente)" class="text-gray-600 font-semibold" />
            <textarea id="wishlist" name="wishlist" rows="3" class="block mt-2 w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 bg-white transition" placeholder="Ex: Gosto de chocolates, livros de suspense..." required></textarea>
            <x-input-error :messages="$errors->get('wishlist')" class="mt-2" />
        </div>

        <div class="mt-8">
            <button type="submit" class="w-full justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-4 rounded-xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 duration-200 text-lg">
                Confirmar Presença 🎉
            </button>
            <a href="{{ route('dashboard') }}" class="block text-center mt-4 text-sm text-gray-400 hover:text-gray-600">Não quero participar</a>
        </div>
    </form>
</x-guest-layout>
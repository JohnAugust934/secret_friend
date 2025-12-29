<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $group->name }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white p-4 sm:p-6 shadow sm:rounded-lg">
                <p class="text-gray-600 text-base sm:text-lg mb-4">{{ $group->description }}</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 bg-gray-50 p-4 rounded-lg">

                    <div class="flex justify-between md:block">
                        <span class="block font-bold text-gray-500 text-xs sm:text-sm uppercase">Data da Revelação</span>
                        <span class="text-base sm:text-lg">{{ $group->event_date->format('d/m/Y') }}</span>
                    </div>

                    <div class="flex justify-between md:block">
                        <span class="block font-bold text-gray-500 text-xs sm:text-sm uppercase">Valor Máximo</span>
                        <span class="text-base sm:text-lg text-green-600 font-bold">R$ {{ number_format($group->budget, 2, ',', '.') }}</span>
                    </div>

                    <div class="col-span-1 md:col-span-2 mt-2 md:mt-0">
                        <span class="block font-bold text-gray-500 text-xs sm:text-sm uppercase mb-1">Link de Convite</span>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                            <code class="w-full sm:w-auto bg-gray-200 px-3 py-2 rounded font-mono text-sm sm:text-lg select-all border border-gray-300 break-all text-center sm:text-left">
                                {{ url('/invite/' . $group->invite_token) }}
                            </code>
                            <span class="text-xs text-gray-500 mt-1 sm:mt-0 mx-auto sm:mx-0">(Toque para copiar)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 shadow sm:rounded-lg">
                <div class="bg-white p-6 shadow sm:rounded-lg mb-6 border-l-4 {{ $group->is_drawn ? 'border-green-500' : 'border-yellow-500' }}">

                    @if(!$group->is_drawn)
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">

                        <div class="mb-2 sm:mb-0">
                            <h3 class="text-lg font-bold text-gray-800">Sorteio Pendente</h3>
                            <p class="text-gray-600 text-sm sm:text-base">Aguardando o administrador realizar o sorteio.</p>
                        </div>

                        @if($group->owner_id === auth()->id())
                        <form action="{{ route('groups.draw', $group) }}" method="POST" onsubmit="return confirm('Tem certeza? Isso não pode ser desfeito.')" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="w-full sm:w-auto bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded shadow flex items-center justify-center gap-2 text-base">
                                🎁 Realizar Sorteio
                            </button>
                        </form>
                        @endif
                    </div>
                    @else
                    <div>
                        <h3 class="text-lg font-bold text-green-700 mb-2">✅ Sorteio Realizado!</h3>

                        @if($myPair)
                        @if($myPair)
                        <div x-data="{ revealed: false }" class="mt-4">

                            <div x-show="!revealed"
                                @click="revealed = true"
                                class="text-center py-10 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 cursor-pointer hover:bg-gray-200 transition group">
                                <p class="text-4xl mb-2 transition transform group-hover:scale-110">🙈</p>
                                <p class="font-bold text-gray-600 text-lg">O sorteio foi realizado!</p>
                                <p class="text-sm text-gray-500">Toque aqui para revelar seu amigo secreto</p>
                                <p class="text-xs text-red-400 mt-2 font-bold uppercase tracking-wider">(Cuidado com os curiosos!)</p>
                            </div>

                            <div x-show="revealed"
                                style="display: none;"
                                @click="revealed = false"
                                class="bg-green-50 p-8 rounded-lg border border-green-200 text-center cursor-pointer hover:bg-green-100 transition animate-fade-in-down relative shadow-sm">

                                <p class="text-xs text-green-600 uppercase font-bold mb-4 tracking-wide border border-green-200 rounded-full inline-block px-3 py-1 bg-white">
                                    Toque em qualquer lugar para esconder 👆
                                </p>

                                <p class="text-gray-600 text-sm uppercase font-bold mb-2">Sua missão secreta é presentear:</p>

                                <div class="py-2">
                                    <p class="text-4xl md:text-5xl text-green-800 font-black mb-1">
                                        {{ $myPair->giftee->name }}
                                    </p>
                                    <p class="text-sm text-gray-600 font-medium">{{ $myPair->giftee->email }}</p>
                                </div>

                                @php
                                $gifteeMember = $group->members->find($myPair->giftee_id);
                                @endphp

                                @if($gifteeMember && $gifteeMember->pivot->wishlist)
                                <div class="mt-6 bg-white p-4 rounded-xl shadow-sm inline-block text-left w-full max-w-md border-l-4 border-green-500">
                                    <p class="text-xs text-gray-400 font-bold uppercase mb-1 flex items-center gap-1">
                                        🎁 Dica de presente:
                                    </p>
                                    <p class="text-gray-800 text-lg italic leading-relaxed">"{{ $gifteeMember->pivot->wishlist }}"</p>
                                </div>
                                @else
                                <p class="text-sm text-gray-400 italic mt-4">(Essa pessoa não preencheu a lista de desejos)</p>
                                @endif

                            </div>
                        </div>
                        @else
                        <div class="bg-red-50 text-red-600 p-4 rounded-lg border border-red-200 text-center">
                            Você não participou deste sorteio.
                        </div>
                        @endif
                        @else
                        <p class="text-red-500">Você não participou deste sorteio.</p>
                        @endif
                    </div>
                    @endif
                </div>

                <ul class="divide-y divide-gray-100">
                    @foreach($group->members as $member)
                    <li class="py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                    {{ substr($member->name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="block font-medium {{ $member->id === auth()->id() ? 'text-indigo-600' : 'text-gray-800' }}">
                                        {{ $member->name }}
                                        @if($member->id === auth()->id() && !$group->is_drawn) (Você) @endif
                                    </span>

                                    <p class="text-sm text-gray-500 italic">
                                        Deseja: "{{ $member->pivot->wishlist ?: 'Nada informado ainda' }}"
                                    </p>
                                </div>
                            </div>

                            @if($member->id === auth()->id() && !$group->is_drawn)
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="text-sm text-indigo-600 hover:text-indigo-800 underline">
                                    Editar
                                </button>

                                <div x-show="open"
                                    @click.away="open = false"
                                    class="absolute right-0 mt-2 w-72 bg-white border rounded shadow-xl z-50 p-4"
                                    style="display: none;">

                                    <form action="{{ route('groups.wishlist.update', $group) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Alterar presente:</label>
                                        <textarea name="wishlist" rows="2" class="w-full text-sm border-gray-300 rounded mb-2 focus:ring-indigo-500">{{ $member->pivot->wishlist }}</textarea>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="open = false" class="text-xs text-gray-500 hover:underline">Cancelar</button>
                                            <button type="submit" class="bg-indigo-600 text-white text-xs px-2 py-1 rounded hover:bg-indigo-700">Salvar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>

        </div>
    </div>
</x-app-layout>
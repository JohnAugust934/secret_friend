<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200">Status Operacional</h2>
            <span class="text-xs px-3 py-1 rounded-full font-bold"
                @class([
                    'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' => $overall === 'ok',
                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300' => $overall === 'warning',
                    'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => $overall === 'fail',
                ])>
                {{ strtoupper($overall) }}
            </span>
        </div>
    </x-slot>

    <div class="py-8 pb-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">Atualizado em {{ $checkedAt->format('d/m/Y H:i:s') }}</p>

            @foreach($checks as $name => $check)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold text-gray-800 dark:text-gray-100 uppercase tracking-wide text-sm">{{ $name }}</h3>
                    <span class="text-[11px] px-2 py-1 rounded-full font-bold"
                        @class([
                            'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' => $check['status'] === 'ok',
                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300' => $check['status'] === 'warning',
                            'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => $check['status'] === 'fail',
                        ])>
                        {{ strtoupper($check['status']) }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $check['details'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</x-app-layout>

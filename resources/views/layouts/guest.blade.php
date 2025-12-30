<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">

    <div x-data="{ loading: false }"
        x-init="
                document.addEventListener('submit', () => { loading = true });
                window.addEventListener('beforeunload', () => { loading = true });
                window.addEventListener('pageshow', (event) => {
                    if (event.persisted) { loading = false; }
                });
             ">

        <div x-show="loading"
            style="display: none;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-gray-900 bg-opacity-80 backdrop-blur-sm">

            <svg class="animate-spin h-16 w-16 text-indigo-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-white text-lg font-semibold animate-pulse">A processar...</p>
        </div>

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-indigo-100 via-purple-50 to-white px-4">

            <div class="mb-6 text-center">
                <a href="/" class="flex flex-col items-center group">
                    <div class="bg-white p-3 rounded-full shadow-md mb-2 group-hover:scale-110 transition duration-300">
                        <x-application-logo class="w-12 h-12 fill-current text-indigo-600" />
                    </div>
                    <span class="text-2xl font-bold text-indigo-900 tracking-tight">Amigo Secreto</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-2 px-6 py-8 bg-white shadow-2xl rounded-2xl border border-gray-100 relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-50 rounded-full opacity-50 pointer-events-none"></div>

                {{ $slot }}
            </div>

            <div class="mt-8 text-xs text-gray-400">
                &copy; {{ date('Y') }} Amigo Secreto da Galera
            </div>
        </div>

    </div>
</body>

</html>
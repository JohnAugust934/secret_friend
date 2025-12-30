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

<body class="font-sans antialiased">

    <div x-data="{ loading: false }"
        x-init="
                window.addEventListener('beforeunload', () => { loading = true });
                window.addEventListener('pageshow', (event) => { if (event.persisted) loading = false; });
             ">
        <div x-show="loading" style="display: none;" class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-gray-900 bg-opacity-80 backdrop-blur-sm">
            <svg class="animate-spin h-16 w-16 text-indigo-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-50 to-white text-gray-900">
            @include('layouts.navigation')

            @isset($header)
            <header class="bg-white/80 backdrop-blur-md shadow-sm border-b border-gray-100 sticky top-0 z-40">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
            @endisset

            <main>
                {{ $slot }}
            </main>

            <footer class="text-center py-8 text-xs text-gray-400">
                &copy; {{ date('Y') }} Amigo Secreto da Galera
            </footer>
        </div>
    </div>
</body>

</html>
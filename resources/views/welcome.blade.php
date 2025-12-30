<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">

    <div x-data="{ loading: false }"
        x-init="
                window.addEventListener('beforeunload', () => { loading = true });
                window.addEventListener('pageshow', (event) => { if (event.persisted) loading = false; });
             ">

        <div x-show="loading"
            style="display: none;"
            class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-gray-900 bg-opacity-80 backdrop-blur-sm">
            <svg class="animate-spin h-16 w-16 text-indigo-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-white text-lg font-semibold animate-pulse">Carregando...</p>
        </div>

        <div class="flex min-h-screen flex-col items-center p-6 lg:p-8">
            <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 flex justify-end">
                @if (Route::has('login'))
                <nav class="flex items-center gap-4">
                    @auth
                    <a href="{{ url('/dashboard') }}" class="inline-block px-5 py-1.5 border border-transparent hover:border-gray-300 rounded-sm text-sm">
                        Dashboard
                    </a>
                    @else
                    <a href="{{ route('login') }}" class="inline-block px-5 py-1.5 border border-transparent hover:border-gray-300 rounded-sm text-sm">
                        Entrar
                    </a>

                    @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="inline-block px-5 py-1.5 border border-transparent hover:border-gray-300 rounded-sm text-sm">
                        Registar
                    </a>
                    @endif
                    @endauth
                </nav>
                @endif
            </header>

            <main class="flex-1 flex items-center justify-center w-full">
                <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl max-w-md w-full text-center border border-gray-100">
                    <div class="text-5xl mb-4">🎅</div>
                    <h2 class="text-3xl font-black mb-4 text-indigo-700">Amigo Secreto</h2>
                    <p class="mb-8 text-gray-600 dark:text-gray-300">
                        Organize os sorteios da família, amigos ou empresa de forma simples, rápida e justa.
                    </p>

                    @auth
                    <a href="{{ route('groups.create') }}" class="inline-block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition transform hover:scale-105">
                        Criar Novo Grupo
                    </a>
                    @else
                    <a href="{{ route('register') }}" class="inline-block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition transform hover:scale-105">
                        Começar Agora
                    </a>
                    <p class="mt-4 text-sm text-gray-500">
                        Já tem conta? <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Faça login</a>
                    </p>
                    @endauth
                </div>
            </main>

            <footer class="mt-10 text-xs text-gray-400">
                &copy; {{ date('Y') }} Amigo Secreto da Galera
            </footer>
        </div>
    </div>
</body>

</html>
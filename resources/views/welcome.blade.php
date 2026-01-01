<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{
          theme: localStorage.getItem('theme') || 'system',
          initTheme() {
              if (this.theme === 'dark' || (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                  document.documentElement.classList.add('dark');
                  this.updateStatusBar('#111827');
              } else {
                  document.documentElement.classList.remove('dark');
                  this.updateStatusBar('#F9FAFB');
              }
          },
          updateStatusBar(color) {
              const meta = document.querySelector('meta[name=\'theme-color\']');
              if(meta) meta.setAttribute('content', color);
          }
      }"
    x-init="initTheme()">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#F9FAFB">
    <title>{{ config('app.name', 'Amigo Secreto') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>

    <style>
        #global-loader {
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
    </style>
</head>

<body class="antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white transition-colors duration-300 min-h-screen flex flex-col justify-center items-center relative overflow-hidden">

    <div id="global-loader" class="fixed inset-0 z-[10000] hidden flex-col items-center justify-center bg-white/50 dark:bg-gray-900/50 transition-opacity duration-300">
        <div class="relative">
            <div class="w-16 h-16 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
            <div class="absolute top-0 left-0 w-16 h-16 border-4 border-transparent border-b-purple-500 rounded-full animate-spin [animation-duration:1.5s]"></div>
        </div>
    </div>

    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-purple-400/30 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob dark:bg-purple-900/40"></div>
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-indigo-400/30 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000 dark:bg-indigo-900/40"></div>
        <div class="absolute -bottom-32 left-1/3 w-96 h-96 bg-pink-400/30 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-4000 dark:bg-pink-900/40"></div>
    </div>

    <div class="max-w-7xl mx-auto p-6 lg:p-8 w-full text-center z-10">
        <div class="flex justify-center mb-8">
            <x-application-logo class="w-32 h-32 fill-current text-indigo-600 dark:text-indigo-400 drop-shadow-2xl animate-bounce-slow" />
        </div>

        <h1 class="text-4xl sm:text-6xl font-black tracking-tight text-gray-900 dark:text-white mb-4 drop-shadow-sm">
            Amigo Secreto <span class="text-indigo-600 dark:text-indigo-400">da Galera</span>
        </h1>

        <p class="mt-4 text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto leading-relaxed">
            Organize seus sorteios de forma rápida, divertida e moderna. Crie grupos, convide amigos e deixe a magia acontecer!
        </p>

        <div class="mt-10 flex flex-col sm:flex-row justify-center gap-4">
            @if (Route::has('login'))
            @auth
            <a href="{{ url('/dashboard') }}" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-indigo-500/50 transition transform hover:-translate-y-1">
                Ir para o Dashboard
            </a>
            @else
            <a href="{{ route('login') }}" class="px-8 py-4 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-900 dark:text-white font-bold rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 transition transform hover:-translate-y-1">
                Entrar
            </a>

            @if (Route::has('register'))
            <a href="{{ route('register') }}" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-indigo-500/50 transition transform hover:-translate-y-1">
                Criar Conta Grátis
            </a>
            @endif
            @endauth
            @endif
        </div>

        <div class="mt-16 text-sm text-gray-500 dark:text-gray-400">
            Feito com ❤️ e Tecnologia de Ponta
        </div>
    </div>

    <script>
        // Loader Script Simplificado para a Landing Page
        document.addEventListener('DOMContentLoaded', () => {
            const loader = document.getElementById('global-loader');
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (link && link.href && !link.target && !link.href.includes('#') && link.hostname === window.location.hostname) {
                    loader.classList.remove('hidden');
                    loader.classList.add('flex');
                }
            });
            window.addEventListener('pageshow', (event) => {
                if (event.persisted) {
                    loader.classList.add('hidden');
                    loader.classList.remove('flex');
                }
            });
        });
    </script>
</body>

</html>
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
          setTheme(val) {
              this.theme = val;
              if (val === 'system') {
                  localStorage.removeItem('theme');
              } else {
                  localStorage.setItem('theme', val);
              }
              this.initTheme();
          },
          updateStatusBar(color) {
              const meta = document.querySelector('meta[name=\'theme-color\']');
              if(meta) meta.setAttribute('content', color);
          }
      }"
    x-init="$watch('theme', () => initTheme()); window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => { if(theme === 'system') initTheme() }); initTheme()">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F9FAFB">

    <title>{{ config('app.name', 'Amigo Secreto') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>

    <style>
        .page-enter {
            animation: fadeInUp 0.4s ease-out forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #global-loader {
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
    </style>
</head>

<body class="font-sans antialiased text-gray-900 dark:text-gray-100 transition-colors duration-300 bg-gray-50 dark:bg-gray-900">

    <x-toast />

    <div class="fixed inset-0 -z-10 h-full w-full bg-gray-50 dark:bg-gray-900">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-indigo-400/30 blur-3xl filter dark:bg-indigo-600/20"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-purple-400/30 blur-3xl filter dark:bg-purple-600/20"></div>
    </div>

    <div id="global-loader" class="fixed inset-0 z-[10000] hidden flex-col items-center justify-center bg-white/50 dark:bg-gray-900/50 transition-opacity duration-300">
        <div class="relative">
            <div class="w-16 h-16 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
            <div class="absolute top-0 left-0 w-16 h-16 border-4 border-transparent border-b-purple-500 rounded-full animate-spin [animation-duration:1.5s]"></div>
        </div>
    </div>

    <div class="min-h-screen flex flex-col">
        @include('layouts.navigation')

        @isset($header)
        <header class="relative z-10">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endisset

        <main class="flex-1 pb-24 sm:pb-0 relative z-10 page-enter">
            {{ $slot }}
        </main>

        <div class="fixed bottom-0 left-0 z-50 w-full h-16 bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg border-t border-gray-200 dark:border-gray-700 flex items-center justify-around sm:hidden shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] transition-all duration-300 safe-area-pb">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ request()->routeIs('dashboard') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="text-[10px] font-medium">Grupos</span>
            </a>
            <div class="relative -top-5">
                <a href="{{ route('groups.create') }}" class="flex items-center justify-center w-14 h-14 rounded-full bg-indigo-600 text-white shadow-lg shadow-indigo-500/40 border-4 border-white dark:border-gray-800 transform transition hover:scale-105 active:scale-95">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </a>
            </div>
            <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ request()->routeIs('profile.edit') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-[10px] font-medium">Perfil</span>
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loader = document.getElementById('global-loader');
            const showLoader = () => {
                loader.classList.remove('hidden');
                loader.classList.add('flex');
            };

            // 1. Intercepta CLIQUES em links (incluindo Logo, Menus, Cards)
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');
                const target = link.getAttribute('target');

                // Verifica se é link interno válido e não abre nova aba
                if (href && !href.startsWith('#') && !href.startsWith('javascript:') && (!target || target === '_self') && (link.hostname === window.location.hostname)) {

                    // Caso especial: Link de logout ou formulários via JS
                    if (link.hasAttribute('onclick') && link.getAttribute('onclick').includes('submit')) {
                        showLoader();
                        return;
                    }

                    // Não mostra loader se for apenas download
                    if (link.hasAttribute('download')) return;

                    showLoader();
                }
            });

            // 2. Intercepta Envios de FORMULÁRIOS (Login, Logout, Salvar)
            document.addEventListener('submit', (e) => {
                if (!e.defaultPrevented) {
                    showLoader();
                }
            });

            // 3. Esconde loader ao usar o botão "Voltar" do navegador
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
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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F9FAFB">

    <title>{{ config('app.name', 'Laravel') }}</title>

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

<body class="font-sans text-gray-900 antialiased bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <div id="global-loader" class="fixed inset-0 z-[10000] hidden flex-col items-center justify-center bg-white/50 dark:bg-gray-900/50 transition-opacity duration-300">
        <div class="relative">
            <div class="w-16 h-16 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
            <div class="absolute top-0 left-0 w-16 h-16 border-4 border-transparent border-b-purple-500 rounded-full animate-spin [animation-duration:1.5s]"></div>
        </div>
    </div>

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative overflow-hidden">
        <div class="absolute top-10 left-10 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-2xl opacity-30 animate-blob dark:bg-purple-900"></div>
        <div class="absolute top-10 right-10 w-72 h-72 bg-indigo-300 rounded-full mix-blend-multiply filter blur-2xl opacity-30 animate-blob animation-delay-2000 dark:bg-indigo-900"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-2xl opacity-30 animate-blob animation-delay-4000 dark:bg-pink-900"></div>

        <div class="relative z-10 mb-6">
            <a href="/">
                <x-application-logo class="w-24 h-24 fill-current text-indigo-600 dark:text-indigo-400 drop-shadow-xl" />
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-white/70 dark:bg-gray-800/60 backdrop-blur-xl border border-white/20 dark:border-gray-700/50 shadow-2xl rounded-3xl overflow-hidden transform transition-all">
            {{ $slot }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loader = document.getElementById('global-loader');
            const showLoader = () => {
                loader.classList.remove('hidden');
                loader.classList.add('flex');
            };

            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link) return;
                const href = link.getAttribute('href');
                if (href && !href.startsWith('#') && (link.hostname === window.location.hostname)) {
                    showLoader();
                }
            });

            document.addEventListener('submit', (e) => {
                if (!e.defaultPrevented) showLoader();
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
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{
          theme: localStorage.getItem('theme') || 'system',
          initTheme() {
              if (this.theme === 'dark' || (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          }
      }"
    x-init="initTheme()">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
</head>

<body class="font-sans text-gray-900 antialiased">

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative overflow-hidden bg-gray-50 dark:bg-gray-900 selection:bg-indigo-500 selection:text-white">

        <div class="absolute top-10 left-10 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-2xl opacity-30 animate-blob dark:bg-purple-900"></div>
        <div class="absolute top-10 right-10 w-72 h-72 bg-indigo-300 rounded-full mix-blend-multiply filter blur-2xl opacity-30 animate-blob animation-delay-2000 dark:bg-indigo-900"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-2xl opacity-30 animate-blob animation-delay-4000 dark:bg-pink-900"></div>

        <div class="relative z-10 mb-6">
            <a href="/">
                <x-application-logo class="w-24 h-24 fill-current text-indigo-600 dark:text-indigo-400 drop-shadow-xl" />
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-white/70 dark:bg-gray-800/60 backdrop-blur-xl border border-white/20 dark:border-gray-700/50 shadow-2xl rounded-3xl overflow-hidden transform transition-all hover:scale-[1.01]">
            {{ $slot }}
        </div>
    </div>
</body>

</html>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gradient-to-br from-indigo-100 via-purple-50 to-white min-h-screen flex items-center justify-center p-6">

    <div class="max-w-4xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row border border-white/50">

        <div class="w-full md:w-1/2 bg-indigo-600 p-12 flex flex-col justify-between text-white relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-6">🎁</div>
                <h1 class="text-4xl font-bold mb-4 tracking-tight">A magia do Natal, simplificada.</h1>
                <p class="text-indigo-100 text-lg leading-relaxed">
                    Esqueça os papeizinhos. Organize sorteios justos, rápidos e divertidos para a sua família, amigos ou empresa.
                </p>
            </div>
            <div class="relative z-10 mt-8 text-sm text-indigo-200">
                &copy; {{ date('Y') }} Amigo Secreto da Galera
            </div>
        </div>

        <div class="w-full md:w-1/2 p-12 flex flex-col justify-center items-center text-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Vamos começar?</h2>
            <p class="text-gray-500 mb-8">Entre ou crie uma conta para gerir os seus grupos.</p>

            <div class="w-full space-y-4 max-w-xs">
                @auth
                <a href="{{ url('/dashboard') }}" class="block w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-1">
                    Ir para o Painel
                </a>
                @else
                <a href="{{ route('login') }}" class="block w-full py-3 px-6 bg-white border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-50 font-bold rounded-xl transition">
                    Entrar
                </a>
                <a href="{{ route('register') }}" class="block w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-1">
                    Criar Conta Grátis
                </a>
                @endauth
            </div>
        </div>
    </div>

</body>

</html>
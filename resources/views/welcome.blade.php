<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GestCar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 sm:py-4 flex justify-between items-center">
                <h1 class="text-xl sm:text-2xl font-bold text-indigo-600">GestCar</h1>
                <nav class="flex items-center gap-2 sm:gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-700 hover:text-indigo-600 font-medium">Painel</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-indigo-600 font-medium touch-manipulation">Entrar</a>
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-sm hover:bg-indigo-700 font-medium touch-manipulation active:bg-indigo-800">Cadastrar</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center px-4 py-8">
            <div class="text-center max-w-2xl">
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-bold text-gray-900">
                    Gestao inteligente para sua estetica automotiva
                </h2>
                <p class="mt-4 sm:mt-6 text-base sm:text-lg text-gray-600">
                    Agende servicos, controle clientes, veiculos e estoque em um so lugar.
                    Simples, rapido e sem papel.
                </p>
                <div class="mt-8 sm:mt-10 flex flex-col sm:flex-row justify-center gap-3 sm:gap-4">
                    @guest
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-6 py-3 sm:py-3.5 rounded-md text-base sm:text-lg font-medium hover:bg-indigo-700 touch-manipulation active:bg-indigo-800 transition">
                            Comece agora
                        </a>
                        <a href="{{ route('login') }}" class="border border-indigo-600 text-indigo-600 px-6 py-3 sm:py-3.5 rounded-md text-base sm:text-lg font-medium hover:bg-indigo-50 touch-manipulation active:bg-indigo-100 transition">
                            Ja tenho conta
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="bg-indigo-600 text-white px-6 py-3 sm:py-3.5 rounded-md text-base sm:text-lg font-medium hover:bg-indigo-700 touch-manipulation active:bg-indigo-800 transition">
                            Ir para o Painel
                        </a>
                    @endguest
                </div>
            </div>
        </main>

        <footer class="py-4 sm:py-6 text-center text-xs sm:text-sm text-gray-400">
            GestCar &copy; {{ date('Y') }} &mdash; Sistema de Gestao para Estetica Automotiva
        </footer>
    </div>
</body>
</html>

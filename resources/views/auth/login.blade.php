<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold text-indigo-600">GestCar</h1>
        <p class="text-sm text-gray-500 mt-1">Acesse sua conta</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="block mt-1 w-full py-2.5" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Senha" />
            <x-text-input id="password" class="block mt-1 w-full py-2.5" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Lembrar-me</span>
            </label>
        </div>

        <div class="mt-6">
            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-md font-medium hover:bg-indigo-700 touch-manipulation active:bg-indigo-800 transition">
                Entrar
            </button>
        </div>

        <div class="flex items-center justify-between mt-4">
            @if (Route::has('password.request'))
                <a class="text-xs text-gray-500 hover:text-gray-900" href="{{ route('password.request') }}">
                    Esqueceu a senha?
                </a>
            @endif

            <a class="text-xs text-indigo-600 hover:text-indigo-900 font-medium" href="{{ route('register') }}">
                Criar conta
            </a>
        </div>
    </form>
</x-guest-layout>

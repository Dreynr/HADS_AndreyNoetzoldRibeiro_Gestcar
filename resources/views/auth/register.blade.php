<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold text-indigo-600">GestCar</h1>
        <p class="text-sm text-gray-500 mt-1">Crie sua conta</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" value="Nome" />
            <x-text-input id="name" class="block mt-1 w-full py-2.5" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="phone" value="Telefone" />
            <x-text-input id="phone" class="block mt-1 w-full py-2.5" type="tel" name="phone" :value="old('phone')" placeholder="(00) 00000-0000" required autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="block mt-1 w-full py-2.5" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Senha" />
            <x-text-input id="password" class="block mt-1 w-full py-2.5" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirmar Senha" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full py-2.5" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6 space-y-3">
            <label class="flex items-start gap-2 cursor-pointer">
                <input type="checkbox" name="privacy" value="1" {{ old('privacy') ? 'checked' : '' }}
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-0.5">
                <span class="text-sm text-gray-700">
                    Li e aceito a
                    <a href="{{ route('privacy') }}" target="_blank"
                       class="text-indigo-600 hover:text-indigo-900 underline">politica de privacidade</a>.
                </span>
            </label>
            <x-input-error :messages="$errors->get('privacy')" />

            <label class="flex items-start gap-2 cursor-pointer">
                <input type="hidden" name="whatsapp_opt_in" value="0">
                <input type="checkbox" name="whatsapp_opt_in" value="1"
                       {{ old('whatsapp_opt_in', '1') ? 'checked' : '' }}
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-0.5">
                <span class="text-sm text-gray-700">
                    Quero receber lembretes e avisos pelo WhatsApp.
                    <span class="block text-xs text-gray-500">
                        Opcional. Desmarcando, voce continua recebendo tudo por e-mail,
                        e pode mudar quando quiser no seu perfil.
                    </span>
                </span>
            </label>
        </div>

        <div class="mt-6">
            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-md font-medium hover:bg-indigo-700 touch-manipulation active:bg-indigo-800 transition">
                Cadastrar
            </button>
        </div>

        <div class="mt-4 text-center">
            <a class="text-xs text-gray-500 hover:text-gray-900" href="{{ route('login') }}">
                Ja tem conta? <span class="text-indigo-600 font-medium">Entre aqui</span>
            </a>
        </div>
    </form>
</x-guest-layout>

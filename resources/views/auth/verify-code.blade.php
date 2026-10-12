<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Digite o codigo de 6 digitos que enviamos para <strong>{{ $email }}</strong>.
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.verify-code') }}">
        @csrf

        <input type="hidden" name="email" value="{{ $email }}">

        <div>
            <x-input-label for="code" value="Codigo de verificacao" />
            <x-text-input
                id="code"
                class="block mt-1 w-full text-center text-2xl tracking-[0.5em] font-mono"
                type="text"
                name="code"
                maxlength="6"
                inputmode="numeric"
                pattern="[0-9]{6}"
                placeholder="000000"
                required
                autofocus
                autocomplete="one-time-code"
            />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <p class="mt-3 text-xs text-gray-500">
            O codigo expira em 15 minutos.
        </p>

        <div class="flex items-center justify-between mt-4">
            <a href="{{ route('password.request') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                Reenviar codigo
            </a>

            <x-primary-button>
                Verificar codigo
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Crie sua nova senha para <strong>{{ $email }}</strong>.
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <div>
            <x-input-label for="password" value="Nova senha" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autofocus autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirmar nova senha" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Redefinir senha
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

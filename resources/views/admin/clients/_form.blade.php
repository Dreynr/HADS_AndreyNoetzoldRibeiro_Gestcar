<div class="space-y-6">
    <div>
        <x-input-label for="name" value="Nome" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $client->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" value="Telefone" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                      :value="old('phone', $client?->formattedPhone() ?? '')" placeholder="(00) 00000-0000" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="E-mail" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                      :value="old('email', $client->email ?? '')" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" :value="isset($client) ? 'Nova Senha (deixe vazio para manter)' : 'Senha'" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                      :required="!isset($client)" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <hr class="border-gray-200">
    <p class="text-sm font-medium text-gray-500">Dados CRM (opcional)</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="birthday" value="Data de nascimento" />
            <x-text-input id="birthday" name="birthday" type="date" class="mt-1 block w-full"
                          :value="old('birthday', isset($client) && $client->birthday ? $client->birthday->format('Y-m-d') : '')" />
        </div>
        <div>
            <x-input-label for="preferred_contact" value="Contato preferido" />
            <select id="preferred_contact" name="preferred_contact"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Nao informado</option>
                <option value="whatsapp" {{ old('preferred_contact', $client->preferred_contact ?? '') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                <option value="telefone" {{ old('preferred_contact', $client->preferred_contact ?? '') === 'telefone' ? 'selected' : '' }}>Telefone</option>
                <option value="email" {{ old('preferred_contact', $client->preferred_contact ?? '') === 'email' ? 'selected' : '' }}>E-mail</option>
            </select>
        </div>
    </div>

    @if(isset($client))
        <div>
            <x-input-label for="preferences" value="Preferencias / observacoes" />
            <textarea id="preferences" name="preferences" rows="2"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      placeholder="Ex: sempre pede cera, prefere horario da noite...">{{ old('preferences', $client->preferences ?? '') }}</textarea>
        </div>
    @endif

    <div class="flex items-center gap-4">
        <x-primary-button>{{ isset($client) ? 'Salvar' : 'Cadastrar' }}</x-primary-button>
        <a href="{{ route('admin.clients.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
    </div>
</div>

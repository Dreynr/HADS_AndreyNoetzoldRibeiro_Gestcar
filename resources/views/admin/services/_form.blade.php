<div class="space-y-6">
    <div>
        <x-input-label for="name" value="Nome do Servico" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $service->name ?? '')" required autofocus
                      placeholder="Ex: Lavagem Completa, Polimento..." />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="type" value="Tipo de Servico" />
        <select id="type" name="type"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="principal" {{ old('type', $service->type ?? 'principal') === 'principal' ? 'selected' : '' }}>Principal (Lavagem)</option>
            <option value="extra" {{ old('type', $service->type ?? 'principal') === 'extra' ? 'selected' : '' }}>Extra / Adicional</option>
        </select>
        <p class="mt-1 text-xs text-gray-500">Servicos principais sao selecionados individualmente. Extras podem ser adicionados junto.</p>
        <x-input-error :messages="$errors->get('type')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" value="Descricao" />
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="Descreva o que o servico inclui...">{{ old('description', $service->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <x-input-label for="price" value="Preco (R$)" />
            <x-text-input id="price" name="price" type="number" step="0.01" min="0.01" class="mt-1 block w-full"
                          :value="old('price', $service->price ?? '')" required
                          placeholder="0,00" />
            <x-input-error :messages="$errors->get('price')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="duration_minutes" value="Duracao (minutos)" />
            <x-text-input id="duration_minutes" name="duration_minutes" type="number" min="15" max="480" class="mt-1 block w-full"
                          :value="old('duration_minutes', $service->duration_minutes ?? '')" required
                          placeholder="Ex: 60, 90, 120..." />
            <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
        </div>
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="blocks_period" value="0">
        <input type="checkbox" id="blocks_period" name="blocks_period" value="1"
               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
               {{ old('blocks_period', $service->blocks_period ?? false) ? 'checked' : '' }}>
        <x-input-label for="blocks_period" value="Reserva o periodo inteiro (ex: lavagem completa)" />
    </div>

    @if(isset($service))
        <div class="flex items-center gap-2">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" id="active" name="active" value="1"
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                   {{ old('active', $service->active) ? 'checked' : '' }}>
            <x-input-label for="active" value="Servico ativo" />
        </div>
    @endif

    <div class="flex items-center gap-4">
        <x-primary-button>{{ isset($service) ? 'Salvar' : 'Cadastrar' }}</x-primary-button>
        <a href="{{ route('admin.services.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
    </div>
</div>

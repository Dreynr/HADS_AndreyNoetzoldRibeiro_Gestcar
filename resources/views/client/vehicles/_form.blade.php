<div class="space-y-6">
    <div>
        <x-input-label for="plate" value="Placa" />
        <x-text-input id="plate" name="plate" type="text" class="mt-1 block w-full"
                      :value="old('plate', $vehicle->plate ?? '')" required autofocus
                      placeholder="ABC1D23 ou ABC-1234" maxlength="10" />
        <x-input-error :messages="$errors->get('plate')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="model" value="Modelo" />
        <x-text-input id="model" name="model" type="text" class="mt-1 block w-full"
                      :value="old('model', $vehicle->model ?? '')" required
                      placeholder="Ex: Civic 2020, Onix Plus..." />
        <x-input-error :messages="$errors->get('model')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="color" value="Cor" />
        <x-text-input id="color" name="color" type="text" class="mt-1 block w-full"
                      :value="old('color', $vehicle->color ?? '')" required
                      placeholder="Ex: Preto, Branco, Prata..." />
        <x-input-error :messages="$errors->get('color')" class="mt-2" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ isset($vehicle) ? 'Salvar' : 'Cadastrar' }}</x-primary-button>
        <a href="{{ route('vehicles.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
    </div>
</div>

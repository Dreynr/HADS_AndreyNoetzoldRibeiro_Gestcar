<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Corrigir Lavagem</h2>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 sm:p-6">

                {{-- Dados que identificam o atendimento: so leitura. Trocar
                     qualquer um deles seria registrar outro fato. --}}
                <dl class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 bg-gray-50 rounded-md p-3 mb-5">
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Cliente</dt>
                        <dd class="text-sm text-gray-900 mt-0.5">{{ $wash->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Veiculo</dt>
                        <dd class="text-sm text-gray-900 mt-0.5">{{ $wash->vehicle->model }} <span class="font-mono text-xs">{{ $wash->vehicle->plate }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Servico</dt>
                        <dd class="text-sm text-gray-900 mt-0.5">{{ $wash->service_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Data</dt>
                        <dd class="text-sm text-gray-900 mt-0.5">{{ $wash->performed_at->format('d/m/Y') }}</dd>
                    </div>
                </dl>

                <p class="text-xs text-gray-500 mb-4">
                    Apenas o valor e as observacoes podem ser corrigidos. Se o cliente, o veiculo,
                    o servico ou a data estiverem errados, registre a lavagem correta.
                </p>

                <form method="POST" action="{{ route('admin.washes.update', $wash) }}">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Valor Cobrado (R$)</label>
                            <input type="number" step="0.01" min="0" id="amount" name="amount"
                                   value="{{ old('amount', number_format($wash->amount, 2, '.', '')) }}"
                                   class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
                            <textarea id="notes" name="notes" rows="3" maxlength="1000"
                                      class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes', $wash->notes) }}</textarea>
                            @error('notes')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-5">
                        <button type="submit" class="px-4 py-2.5 sm:py-2 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700 active:bg-indigo-800 touch-manipulation">
                            Salvar correcao
                        </button>
                        <a href="{{ route('admin.washes.index') }}" class="text-sm text-gray-600 hover:text-gray-900 py-1 touch-manipulation">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

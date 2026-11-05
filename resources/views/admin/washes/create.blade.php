<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Nova Lavagem Avulsa</h2>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 sm:p-6">

                <p class="text-sm text-gray-600 mb-5">
                    Para o cliente que chegou sem agendar. O atendimento que veio de um agendamento
                    e registrado na propria tela do agendamento.
                </p>

                <form method="POST" action="{{ route('admin.washes.store') }}"
                      x-data="{
                          clientId: '{{ old('user_id') }}',
                          serviceId: '{{ old('service_id') }}',
                          amount: '{{ old('amount') }}',
                          vehicles: @js($clients->mapWithKeys(fn ($c) => [$c->id => $c->vehicles->map(fn ($v) => ['id' => $v->id, 'label' => $v->model . ' - ' . $v->plate])->values()])),
                          prices: @js($services->mapWithKeys(fn ($s) => [$s->id => number_format($s->price, 2, '.', '')])),
                          get clientVehicles() { return this.vehicles[this.clientId] ?? [] },
                          fillAmount() { this.amount = this.prices[this.serviceId] ?? '' }
                      }">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                            <select id="user_id" name="user_id" x-model="clientId"
                                    class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Escolha o cliente</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                            @error('user_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="vehicle_id" class="block text-sm font-medium text-gray-700 mb-1">Veiculo</label>
                            {{-- A lista reage ao cliente escolhido: so aparecem os carros dele. --}}
                            <select id="vehicle_id" name="vehicle_id"
                                    class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Escolha o veiculo</option>
                                <template x-for="v in clientVehicles" :key="v.id">
                                    <option :value="v.id" x-text="v.label" :selected="v.id == '{{ old('vehicle_id') }}'"></option>
                                </template>
                            </select>
                            <p class="text-xs text-gray-500 mt-1" x-show="clientId && clientVehicles.length === 0">
                                Esse cliente ainda nao tem veiculo cadastrado.
                            </p>
                            @error('vehicle_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1">Servico</label>
                            <select id="service_id" name="service_id" x-model="serviceId" @change="fillAmount()"
                                    class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Escolha o servico</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }} (R$ {{ number_format($service->price, 2, ',', '.') }})</option>
                                @endforeach
                            </select>
                            @error('service_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Valor Cobrado (R$)</label>
                            {{-- Vem preenchido com o preco do servico, mas o admin pode ajustar. --}}
                            <input type="number" step="0.01" min="0" id="amount" name="amount" x-model="amount"
                                   class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="performed_at" class="block text-sm font-medium text-gray-700 mb-1">Data do Atendimento</label>
                            <input type="date" id="performed_at" name="performed_at" max="{{ today()->format('Y-m-d') }}"
                                   value="{{ old('performed_at', today()->format('Y-m-d')) }}"
                                   class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('performed_at')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Observacoes (opcional)</label>
                            <textarea id="notes" name="notes" rows="2" maxlength="1000"
                                      class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
                            @error('notes')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-5">
                        <button type="submit" class="px-4 py-2.5 sm:py-2 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700 active:bg-indigo-800 touch-manipulation">
                            Registrar lavagem
                        </button>
                        <a href="{{ route('admin.washes.index') }}" class="text-sm text-gray-600 hover:text-gray-900 py-1 touch-manipulation">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

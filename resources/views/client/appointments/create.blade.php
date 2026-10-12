<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Novo Agendamento</h2>
    </x-slot>

    <div class="py-4 sm:py-8 pb-24 sm:pb-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">

                @if($vehicles->isEmpty())
                    <div class="text-center py-4">
                        <p class="text-gray-500 mb-4">Voce precisa cadastrar um veiculo antes de agendar.</p>
                        <a href="{{ route('vehicles.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">
                            Cadastrar Veiculo
                        </a>
                    </div>
                @elseif($services->isEmpty())
                    <div class="text-center py-4">
                        <p class="text-gray-500">Nenhum servico disponivel no momento.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('appointments.store') }}" id="appointmentForm">
                        @csrf

                        <div class="space-y-6">
                            <div>
                                <x-input-label for="vehicle_id" value="Veiculo" />
                                <select id="vehicle_id" name="vehicle_id" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Selecione um veiculo</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->model }} - {{ $vehicle->plate }} ({{ $vehicle->color }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('vehicle_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="service_id" value="Servico" />
                                <select id="service_id" name="service_id" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Selecione um servico</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}"
                                                data-duration="{{ $service->duration_minutes }}"
                                                data-price="{{ number_format($service->price, 2, ',', '.') }}"
                                                {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                            {{ $service->name }} - R$ {{ number_format($service->price, 2, ',', '.') }} ({{ $service->duration_minutes }} min)
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('service_id')" class="mt-2" />
                            </div>

                            <div id="serviceInfo" class="hidden bg-indigo-50 border border-indigo-200 rounded-md p-4">
                                <p class="text-sm text-indigo-700">
                                    Valor: <span id="servicePrice" class="font-semibold"></span> |
                                    Duracao: <span id="serviceDuration" class="font-semibold"></span> min
                                </p>
                            </div>

                            <div>
                                <x-input-label for="date" value="Data" />
                                <x-text-input id="date" type="date" class="mt-1 block w-full"
                                              :value="old('date')" required
                                              min="{{ now()->addDay()->format('Y-m-d') }}" />
                            </div>

                            <div id="slotsContainer" class="hidden">
                                <x-input-label value="Horarios Disponiveis" />
                                <div id="slotsGrid" class="mt-2 grid grid-cols-4 sm:grid-cols-6 gap-2">
                                </div>
                                <div id="slotsLoading" class="hidden text-sm text-gray-500 mt-2">Carregando horarios...</div>
                                <div id="slotsEmpty" class="hidden text-sm text-red-500 mt-2">Nenhum horario disponivel para esta data.</div>
                                <input type="hidden" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}">
                                <x-input-error :messages="$errors->get('scheduled_at')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="notes" value="Observacoes (opcional)" />
                                <textarea id="notes" name="notes" rows="3"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                          placeholder="Alguma observacao sobre o servico...">{{ old('notes') }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>Agendar</x-primary-button>
                                <a href="{{ route('appointments.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                            </div>
                        </div>
                    </form>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const serviceSelect = document.getElementById('service_id');
                            const dateInput = document.getElementById('date');
                            const slotsContainer = document.getElementById('slotsContainer');
                            const slotsGrid = document.getElementById('slotsGrid');
                            const slotsLoading = document.getElementById('slotsLoading');
                            const slotsEmpty = document.getElementById('slotsEmpty');
                            const scheduledAtInput = document.getElementById('scheduled_at');
                            const serviceInfo = document.getElementById('serviceInfo');
                            const servicePrice = document.getElementById('servicePrice');
                            const serviceDuration = document.getElementById('serviceDuration');

                            let selectedSlot = null;

                            serviceSelect.addEventListener('change', function () {
                                const option = this.options[this.selectedIndex];
                                if (this.value) {
                                    servicePrice.textContent = 'R$ ' + option.dataset.price;
                                    serviceDuration.textContent = option.dataset.duration;
                                    serviceInfo.classList.remove('hidden');
                                } else {
                                    serviceInfo.classList.add('hidden');
                                }
                                loadSlots();
                            });

                            dateInput.addEventListener('change', loadSlots);

                            function loadSlots() {
                                const serviceId = serviceSelect.value;
                                const date = dateInput.value;

                                if (!serviceId || !date) {
                                    slotsContainer.classList.add('hidden');
                                    return;
                                }

                                slotsContainer.classList.remove('hidden');
                                slotsGrid.innerHTML = '';
                                slotsLoading.classList.remove('hidden');
                                slotsEmpty.classList.add('hidden');
                                scheduledAtInput.value = '';
                                selectedSlot = null;

                                fetch(`{{ route('appointments.available-slots') }}?service_id=${serviceId}&date=${date}`, {
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then(response => response.json())
                                .then(slots => {
                                    slotsLoading.classList.add('hidden');

                                    if (slots.length === 0) {
                                        slotsEmpty.classList.remove('hidden');
                                        return;
                                    }

                                    slots.forEach(slot => {
                                        const btn = document.createElement('button');
                                        btn.type = 'button';
                                        btn.textContent = slot;
                                        btn.className = 'px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-indigo-50 hover:border-indigo-500 transition-colors';

                                        btn.addEventListener('click', function () {
                                            if (selectedSlot) {
                                                selectedSlot.className = 'px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-indigo-50 hover:border-indigo-500 transition-colors';
                                            }
                                            selectedSlot = btn;
                                            btn.className = 'px-3 py-2 text-sm border-2 border-indigo-600 bg-indigo-600 text-white rounded-md';
                                            scheduledAtInput.value = date + ' ' + slot + ':00';
                                        });

                                        slotsGrid.appendChild(btn);
                                    });
                                })
                                .catch(() => {
                                    slotsLoading.classList.add('hidden');
                                    slotsEmpty.textContent = 'Erro ao carregar horarios. Tente novamente.';
                                    slotsEmpty.classList.remove('hidden');
                                });
                            }
                        });
                    </script>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

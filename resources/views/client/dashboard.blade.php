<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Ola, {{ auth()->user()->name }}!
        </h2>
    </x-slot>

    <div class="py-4 sm:py-8 pb-24 sm:pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if($vehicles->isEmpty())
                <div class="bg-white shadow-sm rounded-lg p-6 sm:p-8 text-center mb-6">
                    <div class="text-4xl mb-3">&#128663;</div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Cadastre seu primeiro veiculo</h3>
                    <p class="text-gray-500 mb-4 text-sm">Para agendar um servico, voce precisa ter pelo menos um veiculo cadastrado.</p>
                    <a href="{{ route('vehicles.create') }}" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700 transition text-sm font-semibold">
                        Cadastrar Veiculo
                    </a>
                </div>
            @else
                {{-- Mobile: proximos agendamentos no topo --}}
                @if($upcomingAppointments->isNotEmpty())
                    <div class="lg:hidden mb-4">
                        <div class="bg-white shadow-sm rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-semibold text-gray-800">Proximos agendamentos</h3>
                                <a href="{{ route('appointments.index') }}" class="text-xs text-indigo-600">Ver todos</a>
                            </div>
                            <div class="flex gap-3 overflow-x-auto pb-1 -mx-1 px-1 snap-x snap-mandatory">
                                @foreach($upcomingAppointments->take(3) as $appt)
                                    <div class="snap-start shrink-0 w-56 border border-gray-100 rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs font-medium text-gray-900 truncate">{{ $appt->service->name }}</span>
                                            <span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold rounded-full shrink-0 ml-1
                                                {{ $appt->status === 'confirmado' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($appt->status) }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            {{ $appt->scheduled_at->format('d/m') }} — {{ \App\Models\Appointment::periodLabel($appt->period) }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

                    {{-- Coluna principal: Calendario + Periodos + Agendamento --}}
                    <div class="lg:col-span-2 space-y-4 sm:space-y-6">

                        {{-- Calendario --}}
                        <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-0.5">Escolha uma data</h3>
                            <p class="text-xs sm:text-sm text-gray-500 mb-3 sm:mb-4">Dias de semana a partir das 17h | Sabados e domingos o dia todo</p>

                            <div class="flex items-center justify-between mb-3">
                                <button type="button" id="prevMonth" class="p-2.5 hover:bg-gray-100 rounded-lg transition active:bg-gray-200 touch-manipulation">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <h4 id="monthLabel" class="text-sm sm:text-base font-semibold text-gray-800"></h4>
                                <button type="button" id="nextMonth" class="p-2.5 hover:bg-gray-100 rounded-lg transition active:bg-gray-200 touch-manipulation">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-7 gap-1 mb-1">
                                <div class="text-center text-[11px] sm:text-xs font-medium text-gray-400 py-1.5">Dom</div>
                                <div class="text-center text-[11px] sm:text-xs font-medium text-gray-400 py-1.5">Seg</div>
                                <div class="text-center text-[11px] sm:text-xs font-medium text-gray-400 py-1.5">Ter</div>
                                <div class="text-center text-[11px] sm:text-xs font-medium text-gray-400 py-1.5">Qua</div>
                                <div class="text-center text-[11px] sm:text-xs font-medium text-gray-400 py-1.5">Qui</div>
                                <div class="text-center text-[11px] sm:text-xs font-medium text-gray-400 py-1.5">Sex</div>
                                <div class="text-center text-[11px] sm:text-xs font-medium text-gray-400 py-1.5">Sab</div>
                            </div>
                            <div id="calendarGrid" class="grid grid-cols-7 gap-1"></div>

                            <div class="mt-3 flex flex-wrap gap-3 text-[11px] sm:text-xs text-gray-500">
                                <span class="flex items-center gap-1">
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-100 border border-indigo-300 inline-block"></span>
                                    Semana (noite)
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="w-2.5 h-2.5 rounded-full bg-green-100 border border-green-300 inline-block"></span>
                                    Fim de semana
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-100 border border-red-300 inline-block"></span>
                                    Lotado
                                </span>
                            </div>
                        </div>

                        {{-- Periodos disponiveis --}}
                        <div id="periodsSection" class="hidden bg-white shadow-sm rounded-lg p-4 sm:p-6">
                            <h3 class="text-sm sm:text-base font-semibold text-gray-800 mb-0.5">
                                Periodos em <span id="selectedDateLabel" class="text-indigo-600"></span>
                            </h3>
                            <p class="text-xs sm:text-sm text-gray-500 mb-3">Selecione o periodo desejado</p>

                            <div id="periodsGrid" class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3"></div>
                            <div id="periodsLoading" class="hidden text-sm text-gray-500 py-4 text-center">Carregando periodos...</div>
                        </div>

                        {{-- Formulario de agendamento --}}
                        <div id="bookingSection" class="hidden bg-white shadow-sm rounded-lg p-4 sm:p-6">
                            <h3 class="text-sm sm:text-base font-semibold text-gray-800 mb-3 sm:mb-4">Finalizar Agendamento</h3>

                            <form method="POST" action="{{ route('appointments.store') }}" id="bookingForm">
                                @csrf
                                <input type="hidden" name="date" id="form_date">
                                <input type="hidden" name="period" id="form_period">

                                <div class="space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-4 mb-4">
                                    <div>
                                        <label for="form_service" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Servico Principal</label>
                                        <select id="form_service" name="service_id" required
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 sm:py-2">
                                            <option value="">Selecione</option>
                                            @foreach($principalServices as $service)
                                                <option value="{{ $service->id }}"
                                                        data-blocks="{{ $service->blocks_period ? '1' : '0' }}"
                                                        data-price="{{ $service->price }}"
                                                        data-duration="{{ $service->duration_minutes }}">
                                                    {{ $service->name }} — R$ {{ number_format($service->price, 2, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="form_vehicle" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Veiculo</label>
                                        <select id="form_vehicle" name="vehicle_id" required
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 sm:py-2">
                                            <option value="">Selecione</option>
                                            @foreach($vehicles as $vehicle)
                                                <option value="{{ $vehicle->id }}">
                                                    {{ $vehicle->model }} — {{ $vehicle->plate }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                @if($extraServices->isNotEmpty())
                                    <div class="mb-4">
                                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Servicos Extras (opcional)</label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach($extraServices as $extra)
                                                <label class="flex items-start gap-2 p-2.5 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                                                    <input type="checkbox" name="extras[]" value="{{ $extra->id }}"
                                                           data-price="{{ $extra->price }}"
                                                           class="extra-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-0.5">
                                                    <div>
                                                        <span class="text-sm font-medium text-gray-900">{{ $extra->name }}</span>
                                                        <span class="text-sm text-indigo-600 font-semibold ml-1">R$ {{ number_format($extra->price, 2, ',', '.') }}</span>
                                                        @if($extra->blocks_period)
                                                            <p class="text-xs text-amber-700 mt-0.5">Reserva o periodo inteiro</p>
                                                        @endif
                                                        @if($extra->description)
                                                            <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($extra->description, 60) }}</p>
                                                        @endif
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div id="serviceDetail" class="hidden bg-indigo-50 border border-indigo-200 rounded-md p-3 mb-3 sm:mb-4">
                                    <div class="text-xs sm:text-sm text-indigo-700">
                                        <p><span id="detailName" class="font-semibold"></span> — R$ <span id="detailPrice" class="font-semibold"></span></p>
                                        <p id="detailExtrasLine" class="hidden mt-1">Extras: <span id="detailExtras"></span></p>
                                        <p class="mt-1 font-bold">Total: R$ <span id="detailTotal"></span></p>
                                    </div>
                                </div>

                                <div class="mb-3 sm:mb-4">
                                    <label for="form_notes" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Observacoes (opcional)</label>
                                    <textarea id="form_notes" name="notes" rows="2"
                                              class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                              placeholder="Alguma observacao..."></textarea>
                                </div>

                                <div id="bookingSummary" class="bg-gray-50 border rounded-md p-3 sm:p-4 mb-4">
                                    <h4 class="text-xs sm:text-sm font-semibold text-gray-700 mb-2">Resumo</h4>
                                    <div class="text-xs sm:text-sm text-gray-600 space-y-1">
                                        <p>Data: <span id="summaryDate" class="font-medium text-gray-900"></span></p>
                                        <p>Periodo: <span id="summaryPeriod" class="font-medium text-gray-900"></span></p>
                                    </div>
                                </div>

                                <button type="submit"
                                        class="w-full bg-indigo-600 text-white py-3.5 sm:py-3 rounded-lg font-semibold hover:bg-indigo-700 active:bg-indigo-800 transition text-sm touch-manipulation">
                                    Confirmar Agendamento
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Coluna lateral (hidden no mobile, conteudo ja aparece acima) --}}
                    <div class="hidden lg:block space-y-6">
                        {{-- Proximos agendamentos --}}
                        <div class="bg-white shadow-sm rounded-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-semibold text-gray-800">Proximos</h3>
                                <a href="{{ route('appointments.index') }}" class="text-xs text-indigo-600 hover:text-indigo-900">Ver todos</a>
                            </div>

                            @if($upcomingAppointments->isEmpty())
                                <p class="text-sm text-gray-500 text-center py-4">Nenhum agendamento marcado.</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($upcomingAppointments as $appt)
                                        <div class="border border-gray-100 rounded-md p-3">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-sm font-medium text-gray-900">{{ $appt->service->name }}</span>
                                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full
                                                    {{ $appt->status === 'confirmado' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ ucfirst($appt->status) }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                {{ $appt->scheduled_at->format('d/m/Y') }} —
                                                {{ \App\Models\Appointment::periodLabel($appt->period) }}
                                            </p>
                                            <p class="text-xs text-gray-400">{{ $appt->vehicle->model }} — {{ $appt->vehicle->plate }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Servicos disponiveis --}}
                        <div class="bg-white shadow-sm rounded-lg p-6">
                            <h3 class="text-base font-semibold text-gray-800 mb-4">Nossos Servicos</h3>
                            <div class="space-y-3">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Principais</p>
                                @foreach($principalServices as $service)
                                    <div class="border border-gray-100 rounded-md p-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-900">{{ $service->name }}</span>
                                            <span class="text-sm font-semibold text-indigo-600">R$ {{ number_format($service->price, 2, ',', '.') }}</span>
                                        </div>
                                        @if($service->description)
                                            <p class="text-xs text-gray-500 mt-1">{{ $service->description }}</p>
                                        @endif
                                    </div>
                                @endforeach

                                @if($extraServices->isNotEmpty())
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-4">Extras</p>
                                    @foreach($extraServices as $service)
                                        <div class="border border-purple-100 rounded-md p-3">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-900">{{ $service->name }}</span>
                                                <span class="text-sm font-semibold text-purple-600">+ R$ {{ number_format($service->price, 2, ',', '.') }}</span>
                                            </div>
                                            @if($service->description)
                                                <p class="text-xs text-gray-500 mt-1">{{ $service->description }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        {{-- Atalhos --}}
                        <div class="space-y-3">
                            <a href="{{ route('vehicles.index') }}" class="block bg-white shadow-sm rounded-lg p-4 hover:shadow-md transition">
                                <h4 class="text-sm font-semibold text-gray-800">Meus Veiculos</h4>
                                <p class="text-xs text-gray-500 mt-1">{{ $vehicles->count() }} {{ $vehicles->count() === 1 ? 'veiculo' : 'veiculos' }}</p>
                            </a>
                            <a href="{{ route('history.index') }}" class="block bg-white shadow-sm rounded-lg p-4 hover:shadow-md transition">
                                <h4 class="text-sm font-semibold text-gray-800">Historico</h4>
                                <p class="text-xs text-gray-500 mt-1">Servicos realizados</p>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarGrid = document.getElementById('calendarGrid');
        if (!calendarGrid) return;

        const monthLabel = document.getElementById('monthLabel');
        const periodsSection = document.getElementById('periodsSection');
        const periodsGrid = document.getElementById('periodsGrid');
        const periodsLoading = document.getElementById('periodsLoading');
        const selectedDateLabel = document.getElementById('selectedDateLabel');
        const bookingSection = document.getElementById('bookingSection');
        const serviceSelect = document.getElementById('form_service');
        const serviceDetail = document.getElementById('serviceDetail');

        const periodsUrl = "{{ route('appointments.available-periods') }}";
        const bookedDatesUrl = "{{ route('appointments.booked-dates') }}";

        const today = new Date();
        let currentMonth = today.getMonth();
        let currentYear = today.getFullYear();
        let selectedDate = null;
        let selectedPeriod = null;
        let fullyBookedDates = [];

        const monthNames = ['Janeiro', 'Fevereiro', 'Marco', 'Abril', 'Maio', 'Junho',
                           'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

        document.getElementById('prevMonth').addEventListener('click', () => { changeMonth(-1); });
        document.getElementById('nextMonth').addEventListener('click', () => { changeMonth(1); });

        const extraCheckboxes = document.querySelectorAll('.extra-checkbox');

        function updatePriceSummary() {
            const opt = serviceSelect.options[serviceSelect.selectedIndex];
            if (!serviceSelect.value) {
                serviceDetail.classList.add('hidden');
                return;
            }

            const serviceName = opt.text.split(' — ')[0];
            const servicePrice = parseFloat(opt.dataset.price) || 0;

            document.getElementById('detailName').textContent = serviceName;
            document.getElementById('detailPrice').textContent = servicePrice.toFixed(2).replace('.', ',');

            let extrasTotal = 0;
            const extraNames = [];
            extraCheckboxes.forEach(cb => {
                if (cb.checked) {
                    extrasTotal += parseFloat(cb.dataset.price) || 0;
                    extraNames.push(cb.closest('label').querySelector('.text-gray-900').textContent);
                }
            });

            const extrasLine = document.getElementById('detailExtrasLine');
            const extrasText = document.getElementById('detailExtras');
            if (extraNames.length > 0) {
                extrasText.textContent = extraNames.join(', ') + ' (+ R$ ' + extrasTotal.toFixed(2).replace('.', ',') + ')';
                extrasLine.classList.remove('hidden');
            } else {
                extrasLine.classList.add('hidden');
            }

            const total = servicePrice + extrasTotal;
            document.getElementById('detailTotal').textContent = total.toFixed(2).replace('.', ',');
            serviceDetail.classList.remove('hidden');
        }

        if (serviceSelect) {
            serviceSelect.addEventListener('change', function () {
                updatePriceSummary();
                if (selectedDate) {
                    loadPeriods(selectedDate);
                }
            });
        }

        // Marcar um extra pode mudar a disponibilidade: se ele reserva o periodo
        // inteiro, periodos que ja tem outro carro deixam de servir.
        extraCheckboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                updatePriceSummary();
                if (selectedDate) {
                    loadPeriods(selectedDate);
                }
            });
        });

        // Rede de protecao: sem periodo escolhido o servidor recusaria o envio.
        const bookingForm = document.getElementById('bookingForm');
        if (bookingForm) {
            bookingForm.addEventListener('submit', function (e) {
                if (!document.getElementById('form_period').value) {
                    e.preventDefault();
                    periodsSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        }

        loadBookedDates().then(() => renderCalendar());

        function changeMonth(delta) {
            currentMonth += delta;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            loadBookedDates().then(() => renderCalendar());
        }

        function loadBookedDates() {
            return fetch(bookedDatesUrl + '?year=' + currentYear + '&month=' + (currentMonth + 1), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(dates => { fullyBookedDates = dates; })
            .catch(() => { fullyBookedDates = []; });
        }

        function renderCalendar() {
            monthLabel.textContent = monthNames[currentMonth] + ' ' + currentYear;
            calendarGrid.innerHTML = '';

            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const todayStr = formatDate(today);

            for (let i = 0; i < firstDay; i++) {
                const empty = document.createElement('div');
                empty.className = 'aspect-square';
                calendarGrid.appendChild(empty);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(currentYear, currentMonth, day);
                const dateStr = formatDate(date);
                const isPast = dateStr <= todayStr;
                const dayOfWeek = date.getDay();
                const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
                const isSelected = selectedDate === dateStr;
                const isFullyBooked = fullyBookedDates.includes(dateStr);

                const cell = document.createElement('button');
                cell.type = 'button';
                cell.textContent = day;

                const base = 'aspect-square flex items-center justify-center text-xs sm:text-sm rounded-lg touch-manipulation transition-all font-medium ';

                if (isPast) {
                    cell.className = base + 'text-gray-300 cursor-not-allowed';
                    cell.disabled = true;
                } else if (isFullyBooked) {
                    cell.className = base + 'bg-red-100 text-red-400 border border-red-200 cursor-not-allowed line-through';
                    cell.disabled = true;
                    cell.title = 'Todos os horarios lotados';
                } else if (isSelected) {
                    cell.className = base + 'bg-indigo-600 text-white font-bold shadow-sm scale-105';
                } else if (isWeekend) {
                    cell.className = base + 'bg-green-50 text-green-800 border border-green-200 hover:bg-green-100 active:bg-green-200 cursor-pointer';
                    cell.addEventListener('click', () => selectDate(dateStr));
                } else {
                    cell.className = base + 'bg-indigo-50 text-indigo-800 border border-indigo-200 hover:bg-indigo-100 active:bg-indigo-200 cursor-pointer';
                    cell.addEventListener('click', () => selectDate(dateStr));
                }

                calendarGrid.appendChild(cell);
            }
        }

        function selectDate(dateStr) {
            selectedDate = dateStr;
            selectedPeriod = null;
            bookingSection.classList.add('hidden');
            renderCalendar();
            loadPeriods(dateStr);
        }

        function loadPeriods(dateStr) {
            const parts = dateStr.split('-');
            selectedDateLabel.textContent = parts[2] + '/' + parts[1] + '/' + parts[0];
            periodsSection.classList.remove('hidden');
            periodsGrid.innerHTML = '';
            periodsLoading.classList.remove('hidden');

            let url = periodsUrl + '?date=' + dateStr;
            if (serviceSelect && serviceSelect.value) {
                url += '&service_id=' + serviceSelect.value;
            }
            extraCheckboxes.forEach(cb => {
                if (cb.checked) {
                    url += '&extras[]=' + encodeURIComponent(cb.value);
                }
            });

            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(periods => {
                periodsLoading.classList.add('hidden');
                periodsGrid.innerHTML = '';

                periods.forEach(p => {
                    const card = document.createElement('button');
                    card.type = 'button';

                    const isSelected = selectedPeriod === p.key;

                    if (!p.available) {
                        card.className = 'p-3.5 sm:p-4 rounded-lg border-2 border-gray-200 bg-gray-50 opacity-60 cursor-not-allowed text-left touch-manipulation';
                        card.disabled = true;
                        card.innerHTML = '<div class="font-semibold text-xs sm:text-sm text-gray-400">' + p.label + '</div>'
                            + '<div class="text-[11px] sm:text-xs text-red-400 mt-1">' + (p.reason || 'Indisponivel') + '</div>';
                    } else if (isSelected) {
                        card.className = 'p-3.5 sm:p-4 rounded-lg border-2 border-indigo-600 bg-indigo-50 text-left shadow-sm touch-manipulation';
                        card.innerHTML = '<div class="font-semibold text-xs sm:text-sm text-indigo-700">' + p.label + '</div>'
                            + '<div class="text-[11px] sm:text-xs text-indigo-500 mt-1">' + p.remaining + ' vaga(s) restante(s)</div>';
                    } else {
                        card.className = 'p-3.5 sm:p-4 rounded-lg border-2 border-green-200 bg-green-50 hover:border-green-400 active:bg-green-200 transition text-left cursor-pointer touch-manipulation';
                        card.innerHTML = '<div class="font-semibold text-xs sm:text-sm text-green-800">' + p.label + '</div>'
                            + '<div class="text-[11px] sm:text-xs text-green-600 mt-1">' + p.remaining + ' vaga(s) restante(s)</div>';
                        card.addEventListener('click', () => selectPeriod(p));
                    }

                    periodsGrid.appendChild(card);
                });

                // O periodo ja escolhido pode ter deixado de servir depois de
                // marcar um extra que reserva o periodo inteiro.
                const chosen = periods.find(p => p.key === selectedPeriod);
                if (selectedPeriod && (!chosen || !chosen.available)) {
                    clearPeriodSelection();
                }

                periodsSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            })
            .catch(() => {
                periodsLoading.classList.add('hidden');
                periodsGrid.innerHTML = '<p class="text-sm text-red-500 col-span-3 text-center py-4">Erro ao carregar. Tente novamente.</p>';
            });
        }

        function selectPeriod(period) {
            selectedPeriod = period.key;
            loadPeriods(selectedDate);

            document.getElementById('form_date').value = selectedDate;
            document.getElementById('form_period').value = period.key;

            const parts = selectedDate.split('-');
            document.getElementById('summaryDate').textContent = parts[2] + '/' + parts[1] + '/' + parts[0];
            document.getElementById('summaryPeriod').textContent = period.label;

            bookingSection.classList.remove('hidden');
            setTimeout(() => {
                bookingSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }

        function clearPeriodSelection() {
            selectedPeriod = null;
            document.getElementById('form_period').value = '';
            document.getElementById('summaryPeriod').textContent = 'selecione um periodo acima';
        }

        function formatDate(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return y + '-' + m + '-' + day;
        }
    });
    </script>
</x-app-layout>

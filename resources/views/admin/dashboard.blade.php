<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Painel Administrativo</h2>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Stats rápidos --}}
            <div class="grid grid-cols-3 gap-2 sm:gap-4 mb-4 sm:mb-6">
                <div class="bg-white shadow-sm rounded-lg p-3 sm:p-4 text-center">
                    <p class="text-xl sm:text-2xl font-bold text-indigo-600">{{ $stats['hoje'] }}</p>
                    <p class="text-[10px] sm:text-sm text-gray-500">Hoje</p>
                </div>
                <div class="bg-yellow-50 shadow-sm rounded-lg p-3 sm:p-4 text-center border border-yellow-200">
                    <p class="text-xl sm:text-2xl font-bold text-yellow-700">{{ $stats['pendente'] }}</p>
                    <p class="text-[10px] sm:text-sm text-yellow-600">Pendentes</p>
                </div>
                <div class="bg-green-50 shadow-sm rounded-lg p-3 sm:p-4 text-center border border-green-200">
                    <p class="text-xl sm:text-2xl font-bold text-green-700">{{ $stats['confirmado'] }}</p>
                    <p class="text-[10px] sm:text-sm text-green-600">Confirmados</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

                {{-- Calendário --}}
                <div class="lg:col-span-2">
                    <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Calendario</h3>

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
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 inline-block"></span>
                                Com agendamentos
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-2.5 h-2.5 rounded-full bg-gray-200 inline-block"></span>
                                Sem agendamentos
                            </span>
                        </div>
                    </div>

                    {{-- Agendamentos do dia selecionado --}}
                    <div id="dayDetail" class="hidden mt-4 bg-white shadow-sm rounded-lg p-4 sm:p-6">
                        <h3 class="text-sm sm:text-base font-semibold text-gray-800 mb-3">
                            Agendamentos em <span id="dayDetailLabel" class="text-indigo-600"></span>
                        </h3>
                        <div id="dayDetailList" class="space-y-2"></div>
                        <div id="dayDetailEmpty" class="hidden text-sm text-gray-400 text-center py-4">Nenhum agendamento neste dia.</div>
                    </div>
                </div>

                {{-- Coluna lateral: atalhos + lista --}}
                <div class="space-y-4 sm:space-y-6">
                    {{-- Atalhos rápidos --}}
                    <div class="grid grid-cols-2 lg:grid-cols-1 gap-2 sm:gap-3">
                        <a href="{{ route('admin.appointments.index') }}" class="bg-white shadow-sm rounded-lg p-3 sm:p-4 hover:shadow-md transition touch-manipulation">
                            <h4 class="text-xs sm:text-sm font-semibold text-gray-800">Agenda</h4>
                            <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5">Dia/Semana/Mes</p>
                        </a>
                        <a href="{{ route('admin.clients.index') }}" class="bg-white shadow-sm rounded-lg p-3 sm:p-4 hover:shadow-md transition touch-manipulation">
                            <h4 class="text-xs sm:text-sm font-semibold text-gray-800">Clientes</h4>
                            <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5">Gerenciar</p>
                        </a>
                        <a href="{{ route('admin.services.index') }}" class="bg-white shadow-sm rounded-lg p-3 sm:p-4 hover:shadow-md transition touch-manipulation">
                            <h4 class="text-xs sm:text-sm font-semibold text-gray-800">Servicos</h4>
                            <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5">Cadastrar/Editar</p>
                        </a>
                        <a href="{{ route('admin.stock.index') }}" class="bg-white shadow-sm rounded-lg p-3 sm:p-4 hover:shadow-md transition touch-manipulation">
                            <h4 class="text-xs sm:text-sm font-semibold text-gray-800">Estoque</h4>
                            <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5">Produtos</p>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Lista de proximos agendamentos --}}
            <div class="mt-4 sm:mt-6 bg-white shadow-sm rounded-lg p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Proximos agendamentos</h3>

                @if($upcomingAppointments->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-6">Nenhum agendamento pendente.</p>
                @else
                    {{-- Mobile: cards --}}
                    <div class="sm:hidden space-y-2">
                        @foreach($upcomingAppointments as $appt)
                            <a href="{{ route('admin.appointments.show', $appt) }}"
                               class="block border rounded-lg p-3 touch-manipulation active:bg-gray-50 {{ $appt->scheduled_at->isToday() ? 'border-indigo-200 bg-indigo-50/30' : 'border-gray-100' }}">
                                <div class="flex items-start justify-between mb-1">
                                    <div class="min-w-0 flex-1 mr-2">
                                        <h4 class="text-xs font-semibold text-gray-900 truncate">{{ $appt->service->name }}</h4>
                                        <p class="text-[11px] text-gray-500">{{ $appt->user->name }}</p>
                                    </div>
                                    @if($appt->status === 'pendente')
                                        <span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold rounded-full bg-yellow-100 text-yellow-800 shrink-0">Pendente</span>
                                    @else
                                        <span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold rounded-full bg-green-100 text-green-800 shrink-0">Confirmado</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-gray-500">
                                    <span>
                                        @if($appt->scheduled_at->isToday())
                                            <span class="font-semibold text-indigo-600">Hoje</span> —
                                        @else
                                            {{ $appt->scheduled_at->format('d/m') }} —
                                        @endif
                                        {{ \App\Models\Appointment::periodLabel($appt->period) }}
                                    </span>
                                    <span class="text-gray-400">{{ $appt->vehicle->model }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- Desktop: tabela --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data/Periodo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Servico</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Veiculo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acoes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($upcomingAppointments as $appt)
                                    <tr class="{{ $appt->scheduled_at->isToday() ? 'bg-indigo-50/40' : '' }}">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <div class="font-medium text-gray-900">
                                                @if($appt->scheduled_at->isToday())
                                                    <span class="text-indigo-600">Hoje</span>
                                                @else
                                                    {{ $appt->scheduled_at->format('d/m/Y') }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500">{{ \App\Models\Appointment::periodLabel($appt->period) }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <div class="font-medium text-gray-900">{{ $appt->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $appt->user->phone ?? $appt->user->email }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $appt->service->name }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $appt->vehicle->model }} - {{ $appt->vehicle->plate }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            @if($appt->status === 'pendente')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendente</span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Confirmado</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                                            <a href="{{ route('admin.appointments.show', $appt) }}" class="text-indigo-600 hover:text-indigo-900 text-xs">Detalhes</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarGrid = document.getElementById('calendarGrid');
        const monthLabel = document.getElementById('monthLabel');
        const dayDetail = document.getElementById('dayDetail');
        const dayDetailLabel = document.getElementById('dayDetailLabel');
        const dayDetailList = document.getElementById('dayDetailList');
        const dayDetailEmpty = document.getElementById('dayDetailEmpty');

        const appointmentsByDate = @json($calendarData);

        const today = new Date();
        let currentMonth = today.getMonth();
        let currentYear = today.getFullYear();
        let selectedDate = null;

        const monthNames = ['Janeiro', 'Fevereiro', 'Marco', 'Abril', 'Maio', 'Junho',
                           'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

        document.getElementById('prevMonth').addEventListener('click', () => { changeMonth(-1); });
        document.getElementById('nextMonth').addEventListener('click', () => { changeMonth(1); });

        renderCalendar();

        function changeMonth(delta) {
            currentMonth += delta;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            renderCalendar();
        }

        function renderCalendar() {
            monthLabel.textContent = monthNames[currentMonth] + ' ' + currentYear;
            calendarGrid.innerHTML = '';

            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const todayStr = formatDate(today);

            for (let i = 0; i < firstDay; i++) {
                calendarGrid.appendChild(Object.assign(document.createElement('div'), { className: 'aspect-square' }));
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(currentYear, currentMonth, day);
                const dateStr = formatDate(date);
                const isToday = dateStr === todayStr;
                const isSelected = selectedDate === dateStr;
                const appts = appointmentsByDate[dateStr] || [];
                const count = appts.length;

                const cell = document.createElement('button');
                cell.type = 'button';

                const base = 'aspect-square flex flex-col items-center justify-center rounded-lg touch-manipulation transition-all relative ';

                if (isSelected) {
                    cell.className = base + 'bg-indigo-600 text-white font-bold shadow-sm scale-105';
                } else if (isToday) {
                    cell.className = base + 'bg-indigo-100 text-indigo-800 border-2 border-indigo-400 font-bold cursor-pointer hover:bg-indigo-200 active:bg-indigo-300';
                } else if (count > 0) {
                    cell.className = base + 'bg-indigo-50 text-indigo-800 border border-indigo-200 font-medium cursor-pointer hover:bg-indigo-100 active:bg-indigo-200';
                } else {
                    cell.className = base + 'text-gray-600 hover:bg-gray-100 active:bg-gray-200 cursor-pointer';
                }

                cell.innerHTML = '<span class="text-xs sm:text-sm">' + day + '</span>';
                if (count > 0) {
                    cell.innerHTML += '<span class="absolute bottom-0.5 sm:bottom-1 flex gap-0.5">';
                    const dots = Math.min(count, 3);
                    for (let d = 0; d < dots; d++) {
                        cell.innerHTML = cell.innerHTML; // rebuild below
                    }
                    let dotsHtml = '<span class="absolute bottom-0.5 sm:bottom-1 flex gap-0.5">';
                    for (let d = 0; d < Math.min(count, 3); d++) {
                        const dotColor = isSelected ? 'bg-white' : 'bg-indigo-500';
                        dotsHtml += '<span class="w-1 h-1 rounded-full ' + dotColor + '"></span>';
                    }
                    dotsHtml += '</span>';
                    cell.innerHTML = '<span class="text-xs sm:text-sm">' + day + '</span>' + dotsHtml;
                }

                cell.addEventListener('click', () => selectDay(dateStr));
                calendarGrid.appendChild(cell);
            }
        }

        function selectDay(dateStr) {
            selectedDate = dateStr;
            renderCalendar();

            const parts = dateStr.split('-');
            dayDetailLabel.textContent = parts[2] + '/' + parts[1] + '/' + parts[0];

            const appts = appointmentsByDate[dateStr] || [];
            dayDetailList.innerHTML = '';

            if (appts.length === 0) {
                dayDetailEmpty.classList.remove('hidden');
            } else {
                dayDetailEmpty.classList.add('hidden');
                appts.forEach(a => {
                    const card = document.createElement('a');
                    card.href = a.url;
                    card.className = 'block border border-gray-100 rounded-lg p-3 hover:bg-gray-50 active:bg-gray-100 touch-manipulation transition';

                    const statusBadge = a.status === 'pendente'
                        ? '<span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold rounded-full bg-yellow-100 text-yellow-800 shrink-0">Pendente</span>'
                        : '<span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold rounded-full bg-green-100 text-green-800 shrink-0">Confirmado</span>';

                    card.innerHTML = '<div class="flex items-start justify-between mb-1">'
                        + '<div class="min-w-0 flex-1 mr-2"><h4 class="text-xs sm:text-sm font-semibold text-gray-900 truncate">' + a.service + '</h4>'
                        + '<p class="text-[11px] sm:text-xs text-gray-500">' + a.client + '</p></div>'
                        + statusBadge + '</div>'
                        + '<div class="flex items-center justify-between text-[11px] sm:text-xs text-gray-500">'
                        + '<span>' + a.period + '</span>'
                        + '<span class="text-gray-400">' + a.vehicle + '</span></div>';

                    dayDetailList.appendChild(card);
                });
            }

            dayDetail.classList.remove('hidden');
            dayDetail.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function formatDate(d) {
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }

        // Auto-select today
        selectDay(formatDate(today));
    });
    </script>
</x-app-layout>

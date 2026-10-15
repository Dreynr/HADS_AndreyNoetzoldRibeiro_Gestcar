<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Agenda</h2>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
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

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 mb-4 sm:mb-6">
                <div class="bg-white shadow-sm rounded-lg p-3 sm:p-4 text-center">
                    <p class="text-xl sm:text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                    <p class="text-xs sm:text-sm text-gray-500">Total</p>
                </div>
                <div class="bg-yellow-50 shadow-sm rounded-lg p-3 sm:p-4 text-center border border-yellow-200">
                    <p class="text-xl sm:text-2xl font-bold text-yellow-700">{{ $stats['pendente'] }}</p>
                    <p class="text-xs sm:text-sm text-yellow-600">Pendentes</p>
                </div>
                <div class="bg-green-50 shadow-sm rounded-lg p-3 sm:p-4 text-center border border-green-200">
                    <p class="text-xl sm:text-2xl font-bold text-green-700">{{ $stats['confirmado'] }}</p>
                    <p class="text-xs sm:text-sm text-green-600">Confirmados</p>
                </div>
                <div class="bg-blue-50 shadow-sm rounded-lg p-3 sm:p-4 text-center border border-blue-200">
                    <p class="text-xl sm:text-2xl font-bold text-blue-700">{{ $stats['concluido'] }}</p>
                    <p class="text-xs sm:text-sm text-blue-600">Concluidos</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6">

                    {{-- Navigation controls --}}
                    <div class="flex flex-col gap-3 mb-4 sm:mb-6">
                        <div class="flex items-center justify-between sm:justify-start sm:gap-2">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.appointments.index', ['view' => $view, 'date' => $view === 'day' ? $baseDate->copy()->subDay()->format('Y-m-d') : ($view === 'week' ? $baseDate->copy()->subWeek()->format('Y-m-d') : $baseDate->copy()->subMonth()->format('Y-m-d'))]) }}"
                                   class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50 active:bg-gray-100 touch-manipulation">
                                    &larr;
                                </a>
                                <a href="{{ route('admin.appointments.index', ['view' => $view, 'date' => now()->format('Y-m-d')]) }}"
                                   class="px-3 py-2 border border-gray-300 rounded-md text-xs sm:text-sm hover:bg-gray-50 active:bg-gray-100 touch-manipulation">
                                    Hoje
                                </a>
                                <a href="{{ route('admin.appointments.index', ['view' => $view, 'date' => $view === 'day' ? $baseDate->copy()->addDay()->format('Y-m-d') : ($view === 'week' ? $baseDate->copy()->addWeek()->format('Y-m-d') : $baseDate->copy()->addMonth()->format('Y-m-d'))]) }}"
                                   class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50 active:bg-gray-100 touch-manipulation">
                                    &rarr;
                                </a>
                            </div>

                            <div class="flex gap-1">
                                <a href="{{ route('admin.appointments.index', ['view' => 'day', 'date' => $date]) }}"
                                   class="px-2.5 sm:px-3 py-2 rounded-md text-xs sm:text-sm touch-manipulation {{ $view === 'day' ? 'bg-indigo-600 text-white' : 'border border-gray-300 hover:bg-gray-50' }}">
                                    Dia
                                </a>
                                <a href="{{ route('admin.appointments.index', ['view' => 'week', 'date' => $date]) }}"
                                   class="px-2.5 sm:px-3 py-2 rounded-md text-xs sm:text-sm touch-manipulation {{ $view === 'week' ? 'bg-indigo-600 text-white' : 'border border-gray-300 hover:bg-gray-50' }}">
                                    Semana
                                </a>
                                <a href="{{ route('admin.appointments.index', ['view' => 'month', 'date' => $date]) }}"
                                   class="px-2.5 sm:px-3 py-2 rounded-md text-xs sm:text-sm touch-manipulation {{ $view === 'month' ? 'bg-indigo-600 text-white' : 'border border-gray-300 hover:bg-gray-50' }}">
                                    Mes
                                </a>
                            </div>
                        </div>

                        <span class="text-sm sm:text-lg font-semibold text-gray-800 text-center sm:text-left">
                            @if($view === 'day')
                                {{ $baseDate->translatedFormat('d \d\e F \d\e Y') }}
                            @elseif($view === 'week')
                                {{ $start->format('d/m') }} - {{ $end->format('d/m/Y') }}
                            @else
                                {{ $baseDate->translatedFormat('F \d\e Y') }}
                            @endif
                        </span>
                    </div>

                    {{-- Filters --}}
                    <form method="GET" action="{{ route('admin.appointments.index') }}" class="mb-4 sm:mb-6">
                        <input type="hidden" name="view" value="{{ $view }}">
                        <input type="hidden" name="date" value="{{ $date }}">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Buscar por cliente..."
                                   class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 sm:py-2">
                            <select name="status" class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 sm:py-2">
                                <option value="">Todos os status</option>
                                <option value="pendente" {{ request('status') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                                <option value="confirmado" {{ request('status') === 'confirmado' ? 'selected' : '' }}>Confirmado</option>
                                <option value="concluido" {{ request('status') === 'concluido' ? 'selected' : '' }}>Concluido</option>
                                <option value="cancelado" {{ request('status') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 sm:flex-none bg-gray-600 text-white px-4 py-2.5 sm:py-2 rounded-md text-sm hover:bg-gray-700 active:bg-gray-800 touch-manipulation">
                                    Filtrar
                                </button>
                                @if(request('search') || request('status'))
                                    <a href="{{ route('admin.appointments.index', ['view' => $view, 'date' => $date]) }}"
                                       class="flex-1 sm:flex-none border border-gray-300 text-gray-600 px-4 py-2.5 sm:py-2 rounded-md text-sm hover:bg-gray-50 text-center touch-manipulation">
                                        Limpar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if($appointments->isEmpty())
                        <p class="text-gray-500 text-center py-8 text-sm">Nenhum agendamento encontrado para este periodo.</p>
                    @else
                        {{-- Mobile: card layout --}}
                        <div class="sm:hidden space-y-3">
                            @foreach($appointments as $appointment)
                                <div class="border rounded-lg p-3.5 {{ $appointment->status === 'cancelado' ? 'opacity-50 border-gray-200' : 'border-gray-100' }}">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="min-w-0 flex-1 mr-2">
                                            <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $appointment->service->name }}</h4>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $appointment->user->name }}</p>
                                        </div>
                                        @switch($appointment->status)
                                            @case('pendente')
                                                <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold rounded-full bg-yellow-100 text-yellow-800 shrink-0">Pendente</span>
                                                @break
                                            @case('confirmado')
                                                <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold rounded-full bg-green-100 text-green-800 shrink-0">Confirmado</span>
                                                @break
                                            @case('concluido')
                                                <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold rounded-full bg-blue-100 text-blue-800 shrink-0">Concluido</span>
                                                @break
                                            @case('cancelado')
                                                <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold rounded-full bg-red-100 text-red-800 shrink-0">Cancelado</span>
                                                @break
                                        @endswitch
                                    </div>

                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                        <span>{{ $appointment->scheduled_at->format('d/m/Y') }} — {{ \App\Models\Appointment::periodLabel($appointment->period) }}</span>
                                        <span class="font-semibold text-gray-900">R$ {{ number_format($appointment->service->price, 2, ',', '.') }}</span>
                                    </div>

                                    <p class="text-xs text-gray-400 mb-2">{{ $appointment->vehicle->model }} - {{ $appointment->vehicle->plate }}</p>

                                    <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                                        <a href="{{ route('admin.appointments.show', $appointment) }}"
                                           class="text-xs text-indigo-600 py-1 touch-manipulation">Detalhes</a>

                                        @if($appointment->status === 'pendente')
                                            <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="confirmado">
                                                <button type="submit" class="px-2.5 py-1.5 text-xs bg-green-100 text-green-800 rounded touch-manipulation active:bg-green-200">Confirmar</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="cancelado">
                                                <button type="submit" class="px-2.5 py-1.5 text-xs bg-red-100 text-red-800 rounded touch-manipulation active:bg-red-200"
                                                        onclick="return confirm('Cancelar este agendamento?')">Cancelar</button>
                                            </form>
                                        @elseif($appointment->status === 'confirmado')
                                            <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="concluido">
                                                <button type="submit" class="px-2.5 py-1.5 text-xs bg-blue-100 text-blue-800 rounded touch-manipulation active:bg-blue-200">Concluir</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="cancelado">
                                                <button type="submit" class="px-2.5 py-1.5 text-xs bg-red-100 text-red-800 rounded touch-manipulation active:bg-red-200"
                                                        onclick="return confirm('Cancelar este agendamento?')">Cancelar</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Desktop: table layout --}}
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Periodo</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Veiculo</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Servico</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($appointments as $appointment)
                                        <tr class="{{ $appointment->status === 'cancelado' ? 'opacity-50' : '' }}">
                                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                <div class="font-medium text-gray-900">{{ $appointment->scheduled_at->format('d/m/Y') }}</div>
                                                <div class="text-gray-500">{{ \App\Models\Appointment::periodLabel($appointment->period) }}</div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                <div class="font-medium text-gray-900">{{ $appointment->user->name }}</div>
                                                <div class="text-gray-500">{{ $appointment->user->phone ?? $appointment->user->email }}</div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $appointment->vehicle->model }} - {{ $appointment->vehicle->plate }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $appointment->service->name }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                R$ {{ number_format($appointment->service->price, 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                @switch($appointment->status)
                                                    @case('pendente')
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendente</span>
                                                        @break
                                                    @case('confirmado')
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Confirmado</span>
                                                        @break
                                                    @case('concluido')
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Concluido</span>
                                                        @break
                                                    @case('cancelado')
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Cancelado</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm">
                                                <div class="flex items-center justify-end gap-1">
                                                    <a href="{{ route('admin.appointments.show', $appointment) }}"
                                                       class="text-indigo-600 hover:text-indigo-900 text-xs mr-2">Detalhes</a>

                                                    @if($appointment->status === 'pendente')
                                                        <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}" class="inline">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="status" value="confirmado">
                                                            <button type="submit" class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded hover:bg-green-200">Confirmar</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}" class="inline">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="status" value="cancelado">
                                                            <button type="submit" class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded hover:bg-red-200"
                                                                    onclick="return confirm('Cancelar este agendamento?')">Cancelar</button>
                                                        </form>
                                                    @elseif($appointment->status === 'confirmado')
                                                        <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}" class="inline">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="status" value="concluido">
                                                            <button type="submit" class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded hover:bg-blue-200">Concluir</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}" class="inline">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="status" value="cancelado">
                                                            <button type="submit" class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded hover:bg-red-200"
                                                                    onclick="return confirm('Cancelar este agendamento?')">Cancelar</button>
                                                        </form>
                                                    @endif
                                                </div>
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
    </div>
</x-app-layout>

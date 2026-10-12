<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Historico de Servicos</h2>
    </x-slot>

    <div class="py-4 sm:py-8 pb-24 sm:pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6">
                    @if($appointments->isEmpty())
                        <p class="text-gray-500 text-center py-8 text-sm">Nenhum agendamento encontrado no historico.</p>
                    @else
                        {{-- Mobile: card layout --}}
                        <div class="sm:hidden space-y-3">
                            @foreach($appointments as $appointment)
                                <div class="border border-gray-100 rounded-lg p-3.5">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-900">{{ $appointment->service->name }}</h4>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $appointment->vehicle->model }} - {{ $appointment->vehicle->plate }}</p>
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
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <span>{{ $appointment->scheduled_at->format('d/m/Y') }} — {{ \App\Models\Appointment::periodLabel($appointment->period) }}</span>
                                        <span class="font-semibold text-gray-900">R$ {{ number_format($appointment->service->price, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Desktop: table layout --}}
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Servico</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Veiculo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($appointments as $appointment)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div>{{ $appointment->scheduled_at->format('d/m/Y') }}</div>
                                                <div class="text-xs text-gray-500">{{ \App\Models\Appointment::periodLabel($appointment->period) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $appointment->service->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $appointment->vehicle->model }} - {{ $appointment->vehicle->plate }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                R$ {{ number_format($appointment->service->price, 2, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
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
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $appointments->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight truncate">{{ $client->name }}</h2>
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('admin.clients.edit', $client) }}" class="bg-indigo-600 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-xs sm:text-sm hover:bg-indigo-700 touch-manipulation">
                    Editar
                </a>
                <a href="{{ route('admin.clients.index') }}" class="border border-gray-300 text-gray-600 px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-xs sm:text-sm hover:bg-gray-50 touch-manipulation">
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Header: info + segmento --}}
            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6 mb-4 sm:mb-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900">{{ $client->name }}</h3>
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ \App\Models\User::segmentColor($stats['segment']) }}">
                                {{ \App\Models\User::segmentLabel($stats['segment']) }}
                            </span>
                            @if($client->active)
                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-1 text-sm text-gray-600">
                            <p>
                                <span class="font-medium text-gray-500">Email:</span>
                                <span class="break-all">{{ $client->email }}</span>
                            </p>
                            <p>
                                <span class="font-medium text-gray-500">Telefone:</span>
                                @if($client->phone)
                                    <a href="tel:{{ $client->phone }}" class="text-indigo-600 sm:text-gray-900">{{ $client->phone }}</a>
                                @else
                                    -
                                @endif
                            </p>
                            @if($client->birthday)
                                <p>
                                    <span class="font-medium text-gray-500">Aniversario:</span>
                                    {{ $client->birthday->format('d/m/Y') }}
                                </p>
                            @endif
                            @if($client->preferred_contact)
                                <p>
                                    <span class="font-medium text-gray-500">Contato preferido:</span>
                                    {{ ucfirst($client->preferred_contact) }}
                                </p>
                            @endif
                            <p>
                                <span class="font-medium text-gray-500">Cliente desde:</span>
                                {{ $client->created_at->format('d/m/Y') }}
                            </p>
                        </div>
                        @if($client->preferences)
                            <div class="mt-2 text-sm">
                                <span class="font-medium text-gray-500">Preferencias:</span>
                                <span class="text-gray-700">{{ $client->preferences }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Stats cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 mb-4 sm:mb-6">
                <div class="bg-white shadow-sm rounded-lg p-3 sm:p-4 text-center">
                    <p class="text-lg sm:text-2xl font-bold text-green-600">R$ {{ number_format($stats['total_spent'], 2, ',', '.') }}</p>
                    <p class="text-[10px] sm:text-xs text-gray-500">Total gasto</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-3 sm:p-4 text-center">
                    <p class="text-lg sm:text-2xl font-bold text-indigo-600">{{ $stats['completed'] }}</p>
                    <p class="text-[10px] sm:text-xs text-gray-500">Concluidos</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-3 sm:p-4 text-center">
                    <p class="text-lg sm:text-2xl font-bold {{ $stats['days_since_last'] !== null && $stats['days_since_last'] > 60 ? 'text-red-600' : 'text-gray-800' }}">
                        {{ $stats['days_since_last'] !== null ? $stats['days_since_last'] . 'd' : '-' }}
                    </p>
                    <p class="text-[10px] sm:text-xs text-gray-500">Ultima visita</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-3 sm:p-4 text-center">
                    <p class="text-lg sm:text-2xl font-bold text-gray-800">{{ $stats['cancelled'] }}</p>
                    <p class="text-[10px] sm:text-xs text-gray-500">Cancelados</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

                {{-- Coluna principal --}}
                <div class="lg:col-span-2 space-y-4 sm:space-y-6">

                    {{-- Servicos favoritos --}}
                    @if($topServices->isNotEmpty())
                        <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                            <h4 class="text-sm sm:text-base font-semibold text-gray-800 mb-3">Servicos mais utilizados</h4>
                            <div class="space-y-2">
                                @foreach($topServices as $svc)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-700">{{ $svc['name'] }}</span>
                                        <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">{{ $svc['count'] }}x</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Historico de agendamentos --}}
                    <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                        <h4 class="text-sm sm:text-base font-semibold text-gray-800 mb-3">Historico de agendamentos</h4>

                        @if($appointments->isEmpty())
                            <p class="text-sm text-gray-400 text-center py-4">Nenhum agendamento registrado.</p>
                        @else
                            <div class="space-y-2">
                                @foreach($appointments->take(10) as $appt)
                                    <a href="{{ route('admin.appointments.show', $appt) }}"
                                       class="block border rounded-lg p-3 touch-manipulation active:bg-gray-50 transition
                                              {{ $appt->status === 'concluido' ? 'border-green-100' : ($appt->status === 'cancelado' ? 'border-red-100 opacity-60' : 'border-gray-100') }}">
                                        <div class="flex items-start justify-between mb-1">
                                            <div class="min-w-0 flex-1 mr-2">
                                                <h5 class="text-xs sm:text-sm font-semibold text-gray-900 truncate">{{ $appt->service->name }}</h5>
                                                <p class="text-[11px] text-gray-500">{{ $appt->vehicle->model }} - {{ $appt->vehicle->plate }}</p>
                                            </div>
                                            @php
                                                $badgeClass = match($appt->status) {
                                                    'concluido' => 'bg-green-100 text-green-800',
                                                    'cancelado' => 'bg-red-100 text-red-800',
                                                    'confirmado' => 'bg-blue-100 text-blue-800',
                                                    default => 'bg-yellow-100 text-yellow-800',
                                                };
                                            @endphp
                                            <span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold rounded-full {{ $badgeClass }} shrink-0">
                                                {{ ucfirst($appt->status) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px] text-gray-500">
                                            <span>{{ $appt->scheduled_at->format('d/m/Y') }} — {{ \App\Models\Appointment::periodLabel($appt->period) }}</span>
                                            <span class="font-medium">R$ {{ number_format($appt->service->price, 2, ',', '.') }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            @if($appointments->count() > 10)
                                <p class="text-xs text-gray-400 text-center mt-3">Mostrando 10 de {{ $appointments->count() }} agendamentos</p>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Coluna lateral: notas + follow-ups + veiculos --}}
                <div class="space-y-4 sm:space-y-6">

                    {{-- Follow-ups pendentes --}}
                    <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                        <h4 class="text-sm sm:text-base font-semibold text-gray-800 mb-3">Follow-ups</h4>

                        @if($pendingFollowUps->isNotEmpty())
                            <div class="space-y-2 mb-4">
                                @foreach($pendingFollowUps as $fu)
                                    <div class="border rounded-lg p-2.5 {{ $fu->isOverdue() ? 'border-red-200 bg-red-50/50' : ($fu->isDueToday() ? 'border-yellow-200 bg-yellow-50/50' : 'border-gray-200 bg-gray-50/50') }}">
                                        <div class="flex items-start justify-between">
                                            <div class="min-w-0 flex-1 mr-2">
                                                <p class="text-xs font-semibold text-gray-900">{{ $fu->title }}</p>
                                                <p class="text-[11px] text-gray-500 mt-0.5">
                                                    {{ $fu->due_date->format('d/m/Y') }}
                                                    @if($fu->isOverdue())
                                                        <span class="text-red-600 font-semibold">(Atrasado)</span>
                                                    @elseif($fu->isDueToday())
                                                        <span class="text-yellow-600 font-semibold">(Hoje)</span>
                                                    @endif
                                                </p>
                                                @if($fu->description)
                                                    <p class="text-[11px] text-gray-500 mt-0.5">{{ $fu->description }}</p>
                                                @endif
                                            </div>
                                            <form method="POST" action="{{ route('admin.clients.follow-ups.complete', [$client, $fu]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-green-600 hover:text-green-800 touch-manipulation p-1" title="Concluir">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-400 mb-4">Nenhum follow-up pendente.</p>
                        @endif

                        {{-- Form novo follow-up --}}
                        <form method="POST" action="{{ route('admin.clients.follow-ups.store', $client) }}" class="border-t pt-3 space-y-2">
                            @csrf
                            <input type="text" name="title" placeholder="Titulo do follow-up" required
                                   class="w-full border-gray-300 rounded-md shadow-sm text-xs focus:border-indigo-500 focus:ring-indigo-500 py-2">
                            <textarea name="description" placeholder="Descricao (opcional)" rows="2"
                                      class="w-full border-gray-300 rounded-md shadow-sm text-xs focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            <div class="grid grid-cols-2 gap-2">
                                <select name="type" required class="border-gray-300 rounded-md shadow-sm text-xs focus:border-indigo-500 focus:ring-indigo-500 py-2">
                                    <option value="retorno">Retorno</option>
                                    <option value="aniversario">Aniversario</option>
                                    <option value="personalizado">Personalizado</option>
                                </select>
                                <input type="date" name="due_date" required min="{{ date('Y-m-d') }}"
                                       class="border-gray-300 rounded-md shadow-sm text-xs focus:border-indigo-500 focus:ring-indigo-500 py-2">
                            </div>
                            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-md text-xs font-medium hover:bg-indigo-700 touch-manipulation active:bg-indigo-800">
                                Adicionar follow-up
                            </button>
                        </form>
                    </div>

                    {{-- Notas do admin --}}
                    <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                        <h4 class="text-sm sm:text-base font-semibold text-gray-800 mb-3">Notas</h4>

                        @if($client->clientNotes->isNotEmpty())
                            <div class="space-y-2 mb-4">
                                @foreach($client->clientNotes->sortByDesc('created_at') as $note)
                                    <div class="border border-gray-100 rounded-lg p-2.5">
                                        <p class="text-xs text-gray-800">{{ $note->content }}</p>
                                        <div class="flex items-center justify-between mt-1.5">
                                            <p class="text-[10px] text-gray-400">{{ $note->admin->name }} — {{ $note->created_at->format('d/m H:i') }}</p>
                                            <form method="POST" action="{{ route('admin.clients.notes.destroy', [$client, $note]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-600 touch-manipulation" title="Remover">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.clients.notes.store', $client) }}" class="border-t pt-3 space-y-2">
                            @csrf
                            <textarea name="content" placeholder="Adicionar nota sobre o cliente..." rows="2" required
                                      class="w-full border-gray-300 rounded-md shadow-sm text-xs focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            <button type="submit" class="w-full bg-gray-700 text-white py-2 rounded-md text-xs font-medium hover:bg-gray-800 touch-manipulation active:bg-gray-900">
                                Salvar nota
                            </button>
                        </form>
                    </div>

                    {{-- Veiculos --}}
                    <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                        <h4 class="text-sm sm:text-base font-semibold text-gray-800 mb-3">Veiculos ({{ $client->vehicles->count() }})</h4>

                        @if($client->vehicles->isNotEmpty())
                            <div class="space-y-2">
                                @foreach($client->vehicles as $vehicle)
                                    <div class="border border-gray-100 rounded-lg p-2.5">
                                        <p class="text-xs font-semibold text-gray-900">{{ $vehicle->model }}</p>
                                        <p class="text-[11px] text-gray-500">{{ $vehicle->plate }} — {{ $vehicle->color ?? '' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-400">Nenhum veiculo cadastrado.</p>
                        @endif
                    </div>

                    {{-- Follow-ups concluidos --}}
                    @if($completedFollowUps->isNotEmpty())
                        <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                            <h4 class="text-sm sm:text-base font-semibold text-gray-800 mb-3">Follow-ups concluidos</h4>
                            <div class="space-y-2">
                                @foreach($completedFollowUps as $fu)
                                    <div class="border border-gray-100 rounded-lg p-2.5 opacity-60">
                                        <p class="text-xs text-gray-700 line-through">{{ $fu->title }}</p>
                                        <p class="text-[10px] text-gray-400">Concluido em {{ $fu->completed_at->format('d/m/Y') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

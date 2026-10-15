<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Detalhes do Agendamento</h2>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

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

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 sm:p-6">

                <div class="mb-5">
                    <div class="flex items-start justify-between mb-3 sm:mb-4">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800">{{ $appointment->service->name }}</h3>
                        @switch($appointment->status)
                            @case('pendente')
                                <span class="inline-flex px-2.5 py-0.5 sm:px-3 sm:py-1 text-xs sm:text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800 shrink-0 ml-2">Pendente</span>
                                @break
                            @case('confirmado')
                                <span class="inline-flex px-2.5 py-0.5 sm:px-3 sm:py-1 text-xs sm:text-sm font-semibold rounded-full bg-green-100 text-green-800 shrink-0 ml-2">Confirmado</span>
                                @break
                            @case('concluido')
                                <span class="inline-flex px-2.5 py-0.5 sm:px-3 sm:py-1 text-xs sm:text-sm font-semibold rounded-full bg-blue-100 text-blue-800 shrink-0 ml-2">Concluido</span>
                                @break
                            @case('cancelado')
                                <span class="inline-flex px-2.5 py-0.5 sm:px-3 sm:py-1 text-xs sm:text-sm font-semibold rounded-full bg-red-100 text-red-800 shrink-0 ml-2">Cancelado</span>
                                @break
                        @endswitch
                    </div>

                    <dl class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Data</dt>
                            <dd class="text-sm text-gray-900 mt-0.5">{{ $appointment->scheduled_at->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Periodo</dt>
                            <dd class="text-sm text-gray-900 mt-0.5">{{ \App\Models\Appointment::periodLabel($appointment->period) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Valor Total</dt>
                            <dd class="text-sm font-semibold text-indigo-600 mt-0.5">R$ {{ number_format($appointment->totalPrice(), 2, ',', '.') }}</dd>
                        </div>
                    </dl>

                    @if($appointment->extras->isNotEmpty())
                        <div class="mt-3">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 mb-1">Servicos Extras</dt>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($appointment->extras as $extra)
                                    <span class="inline-flex px-2 py-0.5 text-xs rounded bg-purple-100 text-purple-700">
                                        {{ $extra->name }} (R$ {{ number_format($extra->pivot->price, 2, ',', '.') }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="border-t pt-4 mb-5">
                    <h4 class="text-xs sm:text-sm font-semibold text-gray-700 mb-2 sm:mb-3">Cliente</h4>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Nome</dt>
                            <dd class="text-sm text-gray-900 mt-0.5">{{ $appointment->user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">E-mail</dt>
                            <dd class="text-sm text-gray-900 mt-0.5 break-all">{{ $appointment->user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Telefone</dt>
                            <dd class="text-sm text-gray-900 mt-0.5">
                                @if($appointment->user->phone)
                                    <a href="tel:{{ $appointment->user->phone }}" class="text-indigo-600 sm:text-gray-900">{{ $appointment->user->phone }}</a>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="border-t pt-4 mb-5">
                    <h4 class="text-xs sm:text-sm font-semibold text-gray-700 mb-2 sm:mb-3">Veiculo</h4>
                    <dl class="grid grid-cols-3 gap-3 sm:gap-4">
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Modelo</dt>
                            <dd class="text-sm text-gray-900 mt-0.5">{{ $appointment->vehicle->model }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Placa</dt>
                            <dd class="text-sm font-mono font-semibold text-gray-900 mt-0.5">{{ $appointment->vehicle->plate }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Cor</dt>
                            <dd class="text-sm text-gray-900 mt-0.5">{{ $appointment->vehicle->color }}</dd>
                        </div>
                    </dl>
                </div>

                @if($appointment->notes)
                    <div class="border-t pt-4 mb-5">
                        <h4 class="text-xs sm:text-sm font-semibold text-gray-700 mb-2">Observacoes</h4>
                        <p class="text-sm text-gray-600">{{ $appointment->notes }}</p>
                    </div>
                @endif

                @if($appointment->admin_notes)
                    <div class="border-t pt-4 mb-5">
                        <h4 class="text-xs sm:text-sm font-semibold text-gray-700 mb-2">Observacao do Admin</h4>
                        <p class="text-sm text-gray-600 bg-gray-50 rounded-md p-3">{{ $appointment->admin_notes }}</p>
                    </div>
                @endif

                <div class="border-t pt-4" x-data="{ showNotes: false }">
                    <h4 class="text-xs sm:text-sm font-semibold text-gray-700 mb-3">Alterar Status</h4>

                    @if(in_array($appointment->status, ['pendente', 'confirmado']))
                        <div class="mb-3">
                            <button type="button" @click="showNotes = !showNotes"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 transition-transform" :class="showNotes ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                Adicionar observacao
                            </button>
                            <div x-show="showNotes" x-transition class="mt-2">
                                <textarea id="admin_notes_input" rows="3" maxlength="1000"
                                          class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                          placeholder="Ex: Motivo do cancelamento, instrucoes para o cliente..."></textarea>
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-2">
                        @if($appointment->status === 'pendente')
                            <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}" class="inline"
                                  onsubmit="var n=document.getElementById('admin_notes_input');if(n)this.querySelector('[name=admin_notes]').value=n.value;">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="confirmado">
                                <input type="hidden" name="admin_notes" value="">
                                <button type="submit" class="px-4 py-2.5 sm:py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 active:bg-green-800 touch-manipulation">Confirmar</button>
                            </form>
                            <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}" class="inline"
                                  onsubmit="var n=document.getElementById('admin_notes_input');if(n)this.querySelector('[name=admin_notes]').value=n.value;">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="cancelado">
                                <input type="hidden" name="admin_notes" value="">
                                <button type="submit" class="px-4 py-2.5 sm:py-2 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 active:bg-red-800 touch-manipulation"
                                        onclick="return confirm('Cancelar este agendamento?')">Cancelar</button>
                            </form>
                        @elseif($appointment->status === 'confirmado')
                            <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}" class="inline"
                                  onsubmit="var n=document.getElementById('admin_notes_input');if(n)this.querySelector('[name=admin_notes]').value=n.value;">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="concluido">
                                <input type="hidden" name="admin_notes" value="">
                                <button type="submit" class="px-4 py-2.5 sm:py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 active:bg-blue-800 touch-manipulation">Concluido</button>
                            </form>
                            <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}" class="inline"
                                  onsubmit="var n=document.getElementById('admin_notes_input');if(n)this.querySelector('[name=admin_notes]').value=n.value;">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="cancelado">
                                <input type="hidden" name="admin_notes" value="">
                                <button type="submit" class="px-4 py-2.5 sm:py-2 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 active:bg-red-800 touch-manipulation"
                                        onclick="return confirm('Cancelar este agendamento?')">Cancelar</button>
                            </form>
                        @elseif($appointment->status === 'cancelado')
                            <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="pendente">
                                <button type="submit" class="px-4 py-2.5 sm:py-2 text-sm bg-yellow-600 text-white rounded-md hover:bg-yellow-700 active:bg-yellow-800 touch-manipulation">Reabrir</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="mt-5 sm:mt-6">
                    <a href="{{ route('admin.appointments.index') }}" class="text-sm text-gray-600 hover:text-gray-900 py-1 touch-manipulation">&larr; Voltar para a agenda</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

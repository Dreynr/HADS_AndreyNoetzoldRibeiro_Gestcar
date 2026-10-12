<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Meus Agendamentos</h2>
            <a href="{{ route('dashboard') }}" class="bg-indigo-600 text-white px-3 sm:px-4 py-2 rounded-md text-xs sm:text-sm hover:bg-indigo-700 active:bg-indigo-800 touch-manipulation font-medium">
                Novo Agendamento
            </a>
        </div>
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

            @if($appointments->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 text-center">
                    <p class="text-gray-500 mb-4 text-sm">Voce nao possui agendamentos pendentes.</p>
                    <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">
                        Fazer meu primeiro agendamento
                    </a>
                </div>
            @else
                <div class="space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 lg:grid-cols-3 sm:gap-6">
                    @foreach($appointments as $appointment)
                        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 sm:p-6">
                            <div class="flex justify-between items-start">
                                <div class="min-w-0 flex-1 mr-2">
                                    <h3 class="text-sm sm:text-lg font-semibold text-gray-800 truncate">{{ $appointment->service->name }}</h3>
                                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ $appointment->vehicle->model }} - {{ $appointment->vehicle->plate }}</p>
                                </div>
                                <span class="inline-flex px-2 py-0.5 sm:py-1 text-[10px] sm:text-xs font-semibold rounded-full shrink-0
                                    {{ $appointment->status === 'confirmado' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </div>

                            <div class="mt-3 space-y-1 text-xs sm:text-sm text-gray-600">
                                <p>Data: <span class="font-medium">{{ $appointment->scheduled_at->format('d/m/Y') }}</span></p>
                                <p>Periodo: <span class="font-medium">{{ \App\Models\Appointment::periodLabel($appointment->period) }}</span></p>
                                <p>Valor: <span class="font-medium">R$ {{ number_format($appointment->totalPrice(), 2, ',', '.') }}</span></p>
                            </div>

                            @if($appointment->extras->isNotEmpty())
                                <div class="mt-2">
                                    <p class="text-xs text-gray-500">Extras:</p>
                                    @foreach($appointment->extras as $extra)
                                        <span class="inline-flex px-1.5 py-0.5 text-[10px] rounded bg-purple-100 text-purple-700 mr-1 mt-1">{{ $extra->name }}</span>
                                    @endforeach
                                </div>
                            @endif

                            @if($appointment->notes)
                                <p class="mt-2 text-xs text-gray-500 italic">{{ $appointment->notes }}</p>
                            @endif

                            <div class="mt-3 sm:mt-4 border-t pt-3">
                                <form method="POST" action="{{ route('appointments.cancel', $appointment) }}"
                                      onsubmit="return confirm('Tem certeza que deseja cancelar este agendamento?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs sm:text-sm text-red-600 hover:text-red-900 active:text-red-950 py-1 touch-manipulation">Cancelar Agendamento</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

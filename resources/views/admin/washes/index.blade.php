<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Lavagens</h2>
            <a href="{{ route('admin.washes.create') }}"
               class="px-3 py-2 text-xs sm:text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700 active:bg-indigo-800 touch-manipulation">
                Nova lavagem avulsa
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 sm:p-6">

                <form method="GET" class="flex flex-wrap items-end gap-3 mb-5">
                    <div>
                        <label for="from" class="block text-xs font-medium text-gray-700 mb-1">De</label>
                        <input type="date" id="from" name="from" value="{{ $from }}"
                               class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="to" class="block text-xs font-medium text-gray-700 mb-1">Ate</label>
                        <input type="date" id="to" name="to" value="{{ $to }}"
                               class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <button type="submit" class="px-4 py-2 text-sm bg-gray-700 text-white rounded-md hover:bg-gray-800 touch-manipulation">Filtrar</button>

                    <div class="ms-auto text-right">
                        <span class="block text-xs text-gray-500">Total do periodo</span>
                        <span class="text-lg font-semibold text-green-700">R$ {{ number_format($total, 2, ',', '.') }}</span>
                    </div>
                </form>

                @if($washes->isEmpty())
                    <p class="text-sm text-gray-500 py-6 text-center">Nenhuma lavagem registrada nesse periodo.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-gray-500 border-b">
                                    <th class="py-2 pe-3">Data</th>
                                    <th class="py-2 pe-3">Cliente</th>
                                    <th class="py-2 pe-3">Veiculo</th>
                                    <th class="py-2 pe-3">Servico</th>
                                    <th class="py-2 pe-3">Origem</th>
                                    <th class="py-2 text-right">Valor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($washes as $wash)
                                    <tr>
                                        <td class="py-2 pe-3 whitespace-nowrap">{{ $wash->performed_at->format('d/m/Y') }}</td>
                                        <td class="py-2 pe-3">{{ $wash->user->name }}</td>
                                        <td class="py-2 pe-3 whitespace-nowrap">{{ $wash->vehicle->model }} <span class="font-mono text-xs text-gray-500">{{ $wash->vehicle->plate }}</span></td>
                                        {{-- O nome gravado na lavagem, nao o nome atual do servico (D-17). --}}
                                        <td class="py-2 pe-3">{{ $wash->service_name }}</td>
                                        <td class="py-2 pe-3">
                                            @if($wash->appointment_id)
                                                <a href="{{ route('admin.appointments.show', $wash->appointment_id) }}" class="text-xs text-indigo-600 hover:text-indigo-800">Agendamento</a>
                                            @else
                                                <span class="text-xs text-gray-500">Avulsa</span>
                                            @endif
                                        </td>
                                        <td class="py-2 text-right font-semibold whitespace-nowrap">R$ {{ number_format($wash->amount, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $washes->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

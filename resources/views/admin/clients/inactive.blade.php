<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Clientes inativos</h2>
            <a href="{{ route('admin.clients.index') }}"
               class="px-3 py-2 text-xs sm:text-sm bg-gray-700 text-white rounded-md hover:bg-gray-800 touch-manipulation">
                Todos os clientes
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

                <p class="text-sm text-gray-500 mb-4">
                    Quem passou de 90 dias sem atendimento, de quem esta ha mais tempo sem vir
                    para quem saiu por ultimo. O contato e manual: o botao abre a conversa no
                    WhatsApp da estetica.
                </p>

                @if($clients->isEmpty())
                    <p class="text-sm text-gray-500 py-6 text-center">
                        Nenhum cliente inativo no momento.
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-gray-500 border-b">
                                    <th class="py-2 pe-3">Cliente</th>
                                    <th class="py-2 pe-3">Contato</th>
                                    <th class="py-2 pe-3 text-right">Sem vir ha</th>
                                    <th class="py-2 pe-3 text-right">Visitas</th>
                                    <th class="py-2 pe-3 text-right">Total gasto</th>
                                    <th class="py-2 text-right">Acoes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($clients as $client)
                                    <tr>
                                        <td class="py-3 pe-3">
                                            <a href="{{ route('admin.clients.show', $client) }}"
                                               class="font-medium text-indigo-600 hover:text-indigo-800">
                                                {{ $client->name }}
                                            </a>
                                            <span class="block text-xs text-gray-400">
                                                ultima visita em {{ $client->lastVisitDate()->format('d/m/Y') }}
                                            </span>
                                        </td>

                                        <td class="py-3 pe-3 text-xs text-gray-600">
                                            @if($client->phone)
                                                <span class="block">{{ $client->formattedPhone() }}</span>
                                            @endif
                                            <span class="block text-gray-400">{{ $client->email }}</span>
                                        </td>

                                        <td class="py-3 pe-3 text-right">
                                            <span class="font-semibold text-red-700">
                                                {{ $client->daysSinceLastVisit() }}
                                            </span>
                                            <span class="text-xs text-gray-500">dias</span>
                                        </td>

                                        <td class="py-3 pe-3 text-right">{{ $client->visitsCount() }}</td>

                                        <td class="py-3 pe-3 text-right">
                                            R$ {{ number_format($client->totalSpent(), 2, ',', '.') }}
                                        </td>

                                        <td class="py-3 text-right whitespace-nowrap">
                                            @if($client->phone)
                                                {{-- Contato manual pelo WhatsApp da estetica. Nunca disparo
                                                     automatico pela Z-API: o consentimento do cliente e para
                                                     avisos dos atendimentos dele (D-05). --}}
                                                <a href="https://wa.me/{{ $client->phone }}"
                                                   target="_blank" rel="noopener"
                                                   class="inline-block px-2 py-1 text-xs bg-green-600 text-white rounded-md hover:bg-green-700 touch-manipulation">
                                                    WhatsApp
                                                </a>
                                            @endif

                                            <form method="POST"
                                                  action="{{ route('admin.clients.follow-ups.store', $client) }}"
                                                  class="inline">
                                                @csrf
                                                <input type="hidden" name="type" value="retorno">
                                                <input type="hidden" name="title" value="Chamar de volta">
                                                <input type="hidden" name="description"
                                                       value="Cliente sem vir ha {{ $client->daysSinceLastVisit() }} dias.">
                                                <input type="hidden" name="due_date" value="{{ today()->format('Y-m-d') }}">
                                                <button type="submit"
                                                        class="px-2 py-1 text-xs bg-indigo-600 text-white rounded-md hover:bg-indigo-700 touch-manipulation">
                                                    Criar retorno
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <p class="mt-4 text-xs text-gray-400">
                        {{ $clients->count() }} {{ $clients->count() === 1 ? 'cliente inativo' : 'clientes inativos' }}.
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

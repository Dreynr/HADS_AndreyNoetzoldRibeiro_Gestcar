@php
    $nomesDosMeses = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Marco', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];

    // A barra do comparativo e proporcional ao maior mes da janela. Sem
    // lavagem nenhuma, o maior e zero e todas as barras ficam vazias.
    $maiorDoComparativo = collect($comparativo)->max('total') ?: 0;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Fechamento mensal</h2>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 sm:p-6">

                <form method="GET" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="mes" class="block text-xs font-medium text-gray-700 mb-1">Mes</label>
                        <select id="mes" name="mes"
                                class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach($nomesDosMeses as $numero => $nome)
                                <option value="{{ $numero }}" @selected($numero === $mes)>{{ $nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="ano" class="block text-xs font-medium text-gray-700 mb-1">Ano</label>
                        <select id="ano" name="ano"
                                class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @for($a = now()->year + 1; $a >= 2025; $a--)
                                <option value="{{ $a }}" @selected($a === $ano)>{{ $a }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit"
                            class="px-4 py-2 text-sm bg-gray-700 text-white rounded-md hover:bg-gray-800 touch-manipulation">
                        Gerar
                    </button>
                </form>

                @error('mes')
                    <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('ano')
                    <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                    <span class="block text-xs text-gray-500">Faturamento do mes</span>
                    <span class="mt-1 block text-2xl font-semibold text-green-700">
                        R$ {{ number_format($fechamento['total'], 2, ',', '.') }}
                    </span>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                    <span class="block text-xs text-gray-500">Lavagens realizadas</span>
                    <span class="mt-1 block text-2xl font-semibold text-gray-800">
                        {{ $fechamento['quantidade'] }}
                    </span>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                    <span class="block text-xs text-gray-500">Ticket medio</span>
                    <span class="mt-1 block text-2xl font-semibold text-gray-800">
                        R$ {{ number_format($fechamento['ticket'], 2, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">
                    Servicos de {{ $nomesDosMeses[$mes] }} de {{ $ano }}
                </h3>

                @if($fechamento['por_servico']->isEmpty())
                    <p class="text-sm text-gray-500 py-6 text-center">
                        Nenhuma lavagem registrada nesse mes.
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-gray-500 border-b">
                                    <th class="py-2 pe-3">Servico</th>
                                    <th class="py-2 pe-3 text-right">Quantidade</th>
                                    <th class="py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($fechamento['por_servico'] as $linha)
                                    <tr>
                                        <td class="py-2 pe-3">{{ $linha->service_name }}</td>
                                        <td class="py-2 pe-3 text-right">{{ $linha->quantidade }}</td>
                                        <td class="py-2 text-right">R$ {{ number_format($linha->total, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t font-semibold">
                                    <td class="py-2 pe-3">Total</td>
                                    <td class="py-2 pe-3 text-right">{{ $fechamento['quantidade'] }}</td>
                                    <td class="py-2 text-right text-green-700">
                                        R$ {{ number_format($fechamento['total'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Ultimos 6 meses</h3>

                <div class="space-y-3">
                    @foreach($comparativo as $linha)
                        @php
                            $largura = $maiorDoComparativo > 0
                                ? round($linha['total'] / $maiorDoComparativo * 100)
                                : 0;
                            $mesAtual = $linha['ano'] === $ano && $linha['mes'] === $mes;
                        @endphp
                        <div class="flex items-center gap-3">
                            <span class="w-16 shrink-0 text-xs {{ $mesAtual ? 'font-semibold text-gray-800' : 'text-gray-500' }}">
                                {{ $linha['rotulo'] }}
                            </span>

                            <div class="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
                                <div class="h-4 rounded-full {{ $mesAtual ? 'bg-indigo-600' : 'bg-indigo-300' }}"
                                     style="width: {{ $largura }}%"></div>
                            </div>

                            <span class="w-32 shrink-0 text-right text-xs text-gray-700">
                                R$ {{ number_format($linha['total'], 2, ',', '.') }}
                                <span class="text-gray-400">({{ $linha['quantidade'] }})</span>
                            </span>
                        </div>
                    @endforeach
                </div>

                <p class="mt-4 text-xs text-gray-400">
                    O numero entre parenteses e a quantidade de lavagens do mes.
                </p>
            </div>

        </div>
    </div>
</x-app-layout>

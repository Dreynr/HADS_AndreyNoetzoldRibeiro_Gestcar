<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
                {{ $product->name }}
            </h2>
            <a href="{{ route('admin.stock.index') }}"
               class="px-3 py-2 text-xs sm:text-sm bg-gray-700 text-white rounded-md hover:bg-gray-800 touch-manipulation">
                Voltar ao estoque
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6 flex items-center justify-between">
                <div>
                    <span class="block text-xs text-gray-500">Saldo atual</span>
                    <span class="text-2xl font-semibold {{ $product->isBelowMinimum() ? 'text-red-700' : 'text-gray-800' }}">
                        {{ number_format($product->quantity, 2, ',', '.') }}
                        <span class="text-base font-normal text-gray-500">{{ $product->unit }}</span>
                    </span>
                </div>
                <div class="text-right">
                    <span class="block text-xs text-gray-500">Estoque minimo</span>
                    <span class="text-sm text-gray-700">
                        {{ number_format($product->min_quantity, 2, ',', '.') }} {{ $product->unit }}
                    </span>
                    @if($product->isBelowMinimum())
                        <span class="mt-1 inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                            Repor
                        </span>
                    @endif
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Registrar movimentacao</h3>

                <form method="POST" action="{{ route('admin.stock.movements.store', $product) }}"
                      class="flex flex-wrap items-end gap-3">
                    @csrf

                    <div>
                        <label for="type" class="block text-xs font-medium text-gray-700 mb-1">Tipo</label>
                        <select id="type" name="type"
                                class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="entrada" @selected(old('type') === 'entrada')>Entrada</option>
                            <option value="saida" @selected(old('type') === 'saida')>Saida</option>
                        </select>
                    </div>

                    <div>
                        <label for="quantity" class="block text-xs font-medium text-gray-700 mb-1">Quantidade</label>
                        <input type="number" step="0.01" min="0.01" id="quantity" name="quantity"
                               value="{{ old('quantity') }}" required
                               class="w-32 text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="flex-1 min-w-48">
                        <label for="notes" class="block text-xs font-medium text-gray-700 mb-1">Observacao</label>
                        <input type="text" id="notes" name="notes" value="{{ old('notes') }}"
                               class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <button type="submit"
                            class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700 touch-manipulation">
                        Registrar
                    </button>
                </form>

                @error('type')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                @error('quantity')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                @error('notes')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Historico</h3>

                @if($movements->isEmpty())
                    <p class="text-sm text-gray-500 py-6 text-center">Nenhuma movimentacao registrada.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-gray-500 border-b">
                                    <th class="py-2 pe-3">Data</th>
                                    <th class="py-2 pe-3">Tipo</th>
                                    <th class="py-2 pe-3 text-right">Quantidade</th>
                                    <th class="py-2">Observacao</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($movements as $movement)
                                    <tr>
                                        <td class="py-2 pe-3 text-gray-600">
                                            {{ $movement->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-2 pe-3">
                                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full
                                                {{ $movement->isIn() ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $movement->label() }}
                                            </span>
                                        </td>
                                        <td class="py-2 pe-3 text-right">
                                            {{ $movement->isIn() ? '+' : '-' }}{{ number_format($movement->quantity, 2, ',', '.') }}
                                            <span class="text-xs text-gray-500">{{ $product->unit }}</span>
                                        </td>
                                        <td class="py-2 text-gray-600">{{ $movement->notes ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $movements->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

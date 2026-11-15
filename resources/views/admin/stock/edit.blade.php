<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Editar produto</h2>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 sm:p-6">

                <form method="POST" action="{{ route('admin.stock.update', $product) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required
                               class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700 mb-1">Unidade</label>
                            <input type="text" id="unit" name="unit" value="{{ old('unit', $product->unit) }}" required
                                   class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('unit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="min_quantity" class="block text-sm font-medium text-gray-700 mb-1">Estoque minimo</label>
                            <input type="number" step="0.01" min="0" id="min_quantity" name="min_quantity"
                                   value="{{ old('min_quantity', $product->min_quantity) }}" required
                                   class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('min_quantity')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-md px-4 py-3 text-sm text-gray-600">
                        Saldo atual: <strong>{{ number_format($product->quantity, 2, ',', '.') }} {{ $product->unit }}</strong>.
                        O saldo nao se edita aqui — ele muda por
                        <a href="{{ route('admin.stock.movements', $product) }}" class="text-indigo-600 underline hover:no-underline">entrada ou saida</a>,
                        para todo numero ter historico.
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700 touch-manipulation">
                            Salvar
                        </button>
                        <a href="{{ route('admin.stock.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Cancelar</a>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Excluir produto</h3>
                <p class="text-xs text-gray-500 mb-3">
                    Produto com movimentacoes registradas e inativado em vez de excluido, para o historico nao se perder.
                </p>
                <form method="POST" action="{{ route('admin.stock.destroy', $product) }}"
                      onsubmit="return confirm('Excluir este produto?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 touch-manipulation">
                        Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

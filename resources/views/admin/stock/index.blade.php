<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Estoque</h2>
            <a href="{{ route('admin.stock.create') }}"
               class="bg-indigo-600 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-xs sm:text-sm hover:bg-indigo-700 touch-manipulation">
                Novo produto
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

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

            {{-- Alerta de estoque minimo (UC07-HU04) --}}
            @if($alerts->isNotEmpty())
                <div class="bg-amber-50 border border-amber-300 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-amber-900 mb-2">
                        {{ $alerts->count() }} {{ $alerts->count() === 1 ? 'produto atingiu' : 'produtos atingiram' }} o estoque minimo
                    </h3>
                    <ul class="text-sm text-amber-800 space-y-1">
                        @foreach($alerts as $alerta)
                            <li>
                                <a href="{{ route('admin.stock.movements', $alerta) }}" class="underline hover:no-underline">
                                    {{ $alerta->name }}
                                </a>
                                — restam {{ number_format($alerta->quantity, 2, ',', '.') }} {{ $alerta->unit }}
                                (minimo {{ number_format($alerta->min_quantity, 2, ',', '.') }})
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 sm:p-6">

                <form method="GET" class="flex flex-wrap items-end gap-3 mb-5">
                    <div class="flex-1 min-w-48">
                        <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Buscar produto</label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                               class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <button type="submit" class="px-4 py-2 text-sm bg-gray-700 text-white rounded-md hover:bg-gray-800 touch-manipulation">
                        Buscar
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.stock.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Limpar</a>
                    @endif
                </form>

                @if($products->isEmpty())
                    <p class="text-sm text-gray-500 py-6 text-center">Nenhum produto cadastrado.</p>
                @else
                    {{-- No celular a tabela obrigava rolar para o lado e os botoes
                         ficavam fora da tela. Abaixo de 640px vira cartao. --}}
                    <div class="sm:hidden space-y-3">
                        @foreach($products as $product)
                            <div class="border rounded-lg p-3 {{ $product->active ? '' : 'opacity-60' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="font-medium text-gray-800">{{ $product->name }}</span>

                                    @if(!$product->active)
                                        <span class="shrink-0 inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">Inativo</span>
                                    @elseif($product->isBelowMinimum())
                                        <span class="shrink-0 inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">Repor</span>
                                    @else
                                        <span class="shrink-0 inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Ok</span>
                                    @endif
                                </div>

                                <p class="mt-1 text-sm {{ $product->isBelowMinimum() ? 'text-red-700 font-semibold' : 'text-gray-700' }}">
                                    {{ number_format($product->quantity, 2, ',', '.') }} {{ $product->unit }}
                                    <span class="text-xs font-normal text-gray-500">
                                        (minimo {{ number_format($product->min_quantity, 2, ',', '.') }})
                                    </span>
                                </p>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if($product->active)
                                        <a href="{{ route('admin.stock.movements', $product) }}"
                                           class="px-3 py-2 text-xs bg-indigo-600 text-white rounded-md touch-manipulation">Movimentar</a>
                                    @endif
                                    <a href="{{ route('admin.stock.edit', $product) }}"
                                       class="px-3 py-2 text-xs bg-gray-600 text-white rounded-md touch-manipulation">Editar</a>
                                    <form method="POST" action="{{ route('admin.stock.toggle-active', $product) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-2 text-xs bg-gray-200 text-gray-700 rounded-md touch-manipulation">
                                            {{ $product->active ? 'Inativar' : 'Ativar' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-gray-500 border-b">
                                    <th class="py-2 pe-3">Produto</th>
                                    <th class="py-2 pe-3 text-right">Saldo</th>
                                    <th class="py-2 pe-3 text-right">Minimo</th>
                                    <th class="py-2 pe-3">Situacao</th>
                                    <th class="py-2 text-right">Acoes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($products as $product)
                                    <tr class="{{ $product->active ? '' : 'opacity-50' }}">
                                        <td class="py-3 pe-3 font-medium text-gray-800">{{ $product->name }}</td>

                                        <td class="py-3 pe-3 text-right {{ $product->isBelowMinimum() ? 'text-red-700 font-semibold' : 'text-gray-700' }}">
                                            {{ number_format($product->quantity, 2, ',', '.') }}
                                            <span class="text-xs text-gray-500">{{ $product->unit }}</span>
                                        </td>

                                        <td class="py-3 pe-3 text-right text-gray-500">
                                            {{ number_format($product->min_quantity, 2, ',', '.') }}
                                        </td>

                                        <td class="py-3 pe-3">
                                            @if(!$product->active)
                                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">Inativo</span>
                                            @elseif($product->isBelowMinimum())
                                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">Repor</span>
                                            @else
                                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Ok</span>
                                            @endif
                                        </td>

                                        <td class="py-3 text-right whitespace-nowrap">
                                            {{-- Produto inativo nao movimenta, entao nao oferece o botao. --}}
                                            @if($product->active)
                                                <a href="{{ route('admin.stock.movements', $product) }}"
                                                   class="inline-block px-2 py-1 text-xs bg-indigo-600 text-white rounded-md hover:bg-indigo-700 touch-manipulation">
                                                    Movimentar
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.stock.edit', $product) }}"
                                               class="inline-block px-2 py-1 text-xs bg-gray-600 text-white rounded-md hover:bg-gray-700 touch-manipulation">
                                                Editar
                                            </a>

                                            <form method="POST" action="{{ route('admin.stock.toggle-active', $product) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="px-2 py-1 text-xs bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 touch-manipulation">
                                                    {{ $product->active ? 'Inativar' : 'Ativar' }}
                                                </button>
                                            </form>
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
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Servicos</h2>
            <a href="{{ route('admin.services.create') }}" class="bg-indigo-600 text-white px-3 sm:px-4 py-2 rounded-md text-xs sm:text-sm hover:bg-indigo-700 active:bg-indigo-800 touch-manipulation font-medium">
                Novo Servico
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
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

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6">
                    <form method="GET" action="{{ route('admin.services.index') }}" class="mb-4 sm:mb-6">
                        <div class="flex gap-2">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Buscar por nome..."
                                   class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 sm:py-2">
                            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700 active:bg-gray-800 touch-manipulation">
                                Buscar
                            </button>
                            @if(request('search'))
                                <a href="{{ route('admin.services.index') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-md text-sm hover:bg-gray-50 touch-manipulation">
                                    Limpar
                                </a>
                            @endif
                        </div>
                    </form>

                    @if($services->isEmpty())
                        <p class="text-gray-500 text-center py-8 text-sm">Nenhum servico encontrado.</p>
                    @else
                        {{-- Mobile: card layout --}}
                        <div class="sm:hidden space-y-3">
                            @foreach($services as $service)
                                <div class="border rounded-lg p-3.5 {{ !$service->active ? 'opacity-50 border-gray-200' : 'border-gray-100' }}">
                                    <div class="flex items-start justify-between mb-1">
                                        <h4 class="text-sm font-semibold text-gray-900">{{ $service->name }}</h4>
                                        <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                            <span class="inline-flex px-1.5 py-0.5 text-[10px] rounded {{ $service->type === 'extra' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                                {{ $service->type === 'extra' ? 'Extra' : 'Principal' }}
                                            </span>
                                            @if($service->blocks_period)
                                                <span class="inline-flex px-1.5 py-0.5 text-[10px] rounded bg-orange-100 text-orange-700">Periodo</span>
                                            @endif
                                            @if($service->active)
                                                <span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                                            @else
                                                <span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($service->description)
                                        <p class="text-xs text-gray-500 mb-2">{{ Str::limit($service->description, 80) }}</p>
                                    @endif

                                    <div class="flex items-center justify-between text-xs text-gray-600 mb-2">
                                        <span class="font-semibold text-indigo-600">R$ {{ number_format($service->price, 2, ',', '.') }}</span>
                                        <span>{{ $service->duration_minutes }} min</span>
                                    </div>

                                    <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                                        <a href="{{ route('admin.services.edit', $service) }}" class="text-xs text-indigo-600 py-1 touch-manipulation">Editar</a>
                                        <form method="POST" action="{{ route('admin.services.toggle-active', $service) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-xs text-yellow-600 py-1 touch-manipulation">
                                                {{ $service->active ? 'Inativar' : 'Ativar' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.services.destroy', $service) }}" class="inline"
                                              onsubmit="return confirm('Tem certeza que deseja excluir este servico?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 py-1 touch-manipulation">Excluir</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Desktop: table layout --}}
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preco</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duracao</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($services as $service)
                                        <tr class="{{ !$service->active ? 'opacity-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $service->name }}</div>
                                                @if($service->description)
                                                    <div class="text-sm text-gray-500">{{ Str::limit($service->description, 60) }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                R$ {{ number_format($service->price, 2, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $service->duration_minutes }} min
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="inline-flex px-2 py-1 text-xs rounded {{ $service->type === 'extra' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                                    {{ $service->type === 'extra' ? 'Extra' : 'Principal' }}
                                                </span>
                                                @if($service->blocks_period)
                                                    <span class="inline-flex px-2 py-1 text-xs rounded bg-orange-100 text-orange-700 ml-1">Reserva periodo</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($service->active)
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                                                @else
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                                <a href="{{ route('admin.services.edit', $service) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>

                                                <form method="POST" action="{{ route('admin.services.toggle-active', $service) }}" class="inline">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900">
                                                        {{ $service->active ? 'Inativar' : 'Ativar' }}
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('admin.services.destroy', $service) }}" class="inline"
                                                      onsubmit="return confirm('Tem certeza que deseja excluir este servico?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $services->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

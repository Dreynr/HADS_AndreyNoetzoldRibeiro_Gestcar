<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Clientes</h2>
            <a href="{{ route('admin.clients.create') }}" class="bg-indigo-600 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-xs sm:text-sm hover:bg-indigo-700 touch-manipulation">
                Novo Cliente
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

            {{-- Follow-ups pendentes (alerta) --}}
            @if($pendingFollowUps->isNotEmpty())
                <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-3 sm:p-4">
                    <h4 class="text-xs sm:text-sm font-semibold text-yellow-800 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        Follow-ups pendentes ({{ $pendingFollowUps->count() }})
                    </h4>
                    <div class="space-y-1.5">
                        @foreach($pendingFollowUps as $fu)
                            <a href="{{ route('admin.clients.show', $fu->client) }}"
                               class="block text-xs touch-manipulation {{ $fu->isOverdue() ? 'text-red-700 font-semibold' : 'text-yellow-700' }}">
                                {{ $fu->due_date->format('d/m') }} — {{ $fu->client->name }}: {{ $fu->title }}
                                @if($fu->isOverdue()) (Atrasado) @elseif($fu->isDueToday()) (Hoje) @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Busca e filtros --}}
            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                <form method="GET" action="{{ route('admin.clients.index') }}" class="mb-4">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Buscar por nome, e-mail ou telefone..."
                               class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                        <div class="flex gap-2">
                            <select name="segment" class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs sm:text-sm py-2">
                                <option value="">Todos</option>
                                <option value="vip" {{ request('segment') === 'vip' ? 'selected' : '' }}>VIP</option>
                                <option value="recorrente" {{ request('segment') === 'recorrente' ? 'selected' : '' }}>Recorrente</option>
                                <option value="novo" {{ request('segment') === 'novo' ? 'selected' : '' }}>Novo</option>
                                <option value="inativo" {{ request('segment') === 'inativo' ? 'selected' : '' }}>Inativo</option>
                            </select>
                            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-md text-xs sm:text-sm hover:bg-gray-700 touch-manipulation shrink-0">
                                Buscar
                            </button>
                        </div>
                        @if(request('search') || request('segment'))
                            <a href="{{ route('admin.clients.index') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-md text-xs sm:text-sm hover:bg-gray-50 text-center touch-manipulation shrink-0">
                                Limpar
                            </a>
                        @endif
                    </div>
                </form>

                @if($clients->isEmpty())
                    <p class="text-gray-500 text-center py-8 text-sm">Nenhum cliente encontrado.</p>
                @else
                    {{-- Mobile: cards --}}
                    <div class="sm:hidden space-y-2">
                        @foreach($clients as $client)
                            <a href="{{ route('admin.clients.show', $client) }}"
                               class="block border rounded-lg p-3 touch-manipulation active:bg-gray-50 transition {{ !$client->active ? 'opacity-50' : '' }}">
                                <div class="flex items-start justify-between mb-1">
                                    <div class="min-w-0 flex-1 mr-2">
                                        <h4 class="text-xs font-semibold text-gray-900 truncate">{{ $client->name }}</h4>
                                        <p class="text-[11px] text-gray-500">{{ $client->email }}</p>
                                    </div>
                                    <div class="flex gap-1 shrink-0">
                                        @php $seg = $client->segment(); @endphp
                                        <span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold rounded-full {{ \App\Models\User::segmentColor($seg) }}">
                                            {{ \App\Models\User::segmentLabel($seg) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-gray-500">
                                    <span>{{ $client->formattedPhone() ?? '-' }}</span>
                                    <span>{{ $client->completed_count ?? 0 }} servicos</span>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- Desktop: tabela --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contato</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Segmento</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Servicos</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acoes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($clients as $client)
                                    <tr class="{{ !$client->active ? 'opacity-50' : '' }}">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <a href="{{ route('admin.clients.show', $client) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                                {{ $client->name }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            <div>{{ $client->email }}</div>
                                            <div class="text-xs">{{ $client->formattedPhone() ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            @php $seg = $client->segment(); @endphp
                                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ \App\Models\User::segmentColor($seg) }}">
                                                {{ \App\Models\User::segmentLabel($seg) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                            {{ $client->completed_count ?? 0 }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            @if($client->active)
                                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                                            @else
                                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm space-x-2">
                                            <a href="{{ route('admin.clients.show', $client) }}" class="text-indigo-600 hover:text-indigo-900 text-xs">Ficha</a>
                                            <a href="{{ route('admin.clients.edit', $client) }}" class="text-gray-600 hover:text-gray-900 text-xs">Editar</a>
                                            <form method="POST" action="{{ route('admin.clients.toggle-active', $client) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-yellow-600 hover:text-yellow-900 text-xs">
                                                    {{ $client->active ? 'Inativar' : 'Ativar' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $clients->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

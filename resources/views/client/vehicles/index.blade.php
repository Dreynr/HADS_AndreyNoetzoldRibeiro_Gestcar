<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Meus Ve&iacute;culos</h2>
            <a href="{{ route('vehicles.create') }}" class="bg-indigo-600 text-white px-3 sm:px-4 py-2 rounded-md text-xs sm:text-sm hover:bg-indigo-700 active:bg-indigo-800 touch-manipulation font-medium">
                Novo Ve&iacute;culo
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

            @if($vehicles->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 text-center">
                    <p class="text-gray-500 mb-4 text-sm">Voc&ecirc; ainda n&atilde;o cadastrou nenhum ve&iacute;culo.</p>
                    <a href="{{ route('vehicles.create') }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">
                        Cadastrar meu primeiro ve&iacute;culo
                    </a>
                </div>
            @else
                <div class="space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 lg:grid-cols-3 sm:gap-6">
                    @foreach($vehicles as $vehicle)
                        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4 sm:p-6">
                            <div>
                                <h3 class="text-sm sm:text-lg font-semibold text-gray-800">{{ $vehicle->model }}</h3>
                                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Placa: <span class="font-mono font-semibold">{{ $vehicle->plate }}</span></p>
                                <p class="text-xs sm:text-sm text-gray-500">Cor: {{ $vehicle->color }}</p>
                            </div>
                            <div class="mt-3 sm:mt-4 flex gap-4 border-t pt-3 sm:pt-4">
                                <a href="{{ route('vehicles.edit', $vehicle) }}" class="text-xs sm:text-sm text-indigo-600 hover:text-indigo-900 py-1 touch-manipulation">Editar</a>
                                <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}"
                                      onsubmit="return confirm('Tem certeza que deseja excluir este ve&iacute;culo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs sm:text-sm text-red-600 hover:text-red-900 py-1 touch-manipulation">Excluir</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Editar Veiculo</h2>
    </x-slot>

    <div class="py-4 sm:py-8 pb-24 sm:pb-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
                <form method="POST" action="{{ route('vehicles.update', $vehicle) }}">
                    @csrf
                    @method('PUT')
                    @include('client.vehicles._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

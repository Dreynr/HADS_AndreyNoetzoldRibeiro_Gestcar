<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Painel
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(auth()->user()->isAdmin())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <a href="{{ route('admin.clients.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                        <h3 class="text-lg font-semibold text-gray-800">Clientes</h3>
                        <p class="text-sm text-gray-500 mt-1">Gerenciar clientes cadastrados</p>
                    </a>
                    <a href="{{ route('admin.services.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                        <h3 class="text-lg font-semibold text-gray-800">Servi&ccedil;os</h3>
                        <p class="text-sm text-gray-500 mt-1">Cadastrar e editar servi&ccedil;os</p>
                    </a>
                    <a href="{{ route('admin.appointments.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                        <h3 class="text-lg font-semibold text-gray-800">Agenda</h3>
                        <p class="text-sm text-gray-500 mt-1">Ver agendamentos do dia</p>
                    </a>
                    <a href="{{ route('admin.stock.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                        <h3 class="text-lg font-semibold text-gray-800">Estoque</h3>
                        <p class="text-sm text-gray-500 mt-1">Controle de produtos</p>
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                        <h3 class="text-lg font-semibold text-gray-800">Relat&oacute;rios</h3>
                        <p class="text-sm text-gray-500 mt-1">Fechamento mensal e CRM</p>
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <a href="{{ route('vehicles.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                        <h3 class="text-lg font-semibold text-gray-800">Meus Ve&iacute;culos</h3>
                        <p class="text-sm text-gray-500 mt-1">Cadastrar e gerenciar ve&iacute;culos</p>
                    </a>
                    <a href="{{ route('appointments.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                        <h3 class="text-lg font-semibold text-gray-800">Meus Agendamentos</h3>
                        <p class="text-sm text-gray-500 mt-1">Agendar e ver seus hor&aacute;rios</p>
                    </a>
                    <a href="{{ route('history.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                        <h3 class="text-lg font-semibold text-gray-800">Hist&oacute;rico</h3>
                        <p class="text-sm text-gray-500 mt-1">Servi&ccedil;os realizados nos seus ve&iacute;culos</p>
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

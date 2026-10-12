<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\VehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(): View
    {
        $vehicles = auth()->user()->vehicles()->latest()->get();

        return view('client.vehicles.index', compact('vehicles'));
    }

    public function create(): View
    {
        return view('client.vehicles.create');
    }

    public function store(VehicleRequest $request): RedirectResponse
    {
        auth()->user()->vehicles()->create($request->validated());

        return redirect()->route('vehicles.index')
            ->with('success', 'Veículo cadastrado com sucesso.');
    }

    public function edit(Vehicle $vehicle): View
    {
        $this->authorizeVehicle($vehicle);

        return view('client.vehicles.edit', compact('vehicle'));
    }

    public function update(VehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeVehicle($vehicle);
        $vehicle->update($request->validated());

        return redirect()->route('vehicles.index')
            ->with('success', 'Veículo atualizado com sucesso.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeVehicle($vehicle);

        if ($vehicle->appointments()->exists()) {
            return redirect()->route('vehicles.index')
                ->with('error', 'Este veículo possui agendamentos e não pode ser excluído.');
        }

        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', 'Veículo excluído com sucesso.');
    }

    private function authorizeVehicle(Vehicle $vehicle): void
    {
        abort_unless($vehicle->user_id === auth()->id(), 403);
    }
}

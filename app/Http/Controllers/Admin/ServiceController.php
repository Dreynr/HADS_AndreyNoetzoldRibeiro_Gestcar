<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Service::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $services = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.services.index', compact('services'));
    }

    public function create(): View
    {
        return view('admin.services.create');
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        Service::create($request->validated());

        return redirect()->route('admin.services.index')
            ->with('success', 'Servico cadastrado com sucesso.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return redirect()->route('admin.services.index')
            ->with('success', 'Servico atualizado com sucesso.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        if ($service->appointments()->whereIn('status', ['pendente', 'confirmado'])->exists()) {
            return redirect()->route('admin.services.index')
                ->with('error', 'Nao e possivel excluir um servico com agendamentos pendentes ou confirmados.');
        }

        if ($service->appointments()->exists()) {
            $service->update(['active' => false]);

            return redirect()->route('admin.services.index')
                ->with('success', 'Servico inativado (possui agendamentos vinculados).');
        }

        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Servico excluido com sucesso.');
    }

    public function toggleActive(Service $service): RedirectResponse
    {
        $service->update(['active' => !$service->active]);
        $status = $service->active ? 'ativado' : 'inativado';

        return redirect()->route('admin.services.index')
            ->with('success', "Servico {$status} com sucesso.");
    }
}

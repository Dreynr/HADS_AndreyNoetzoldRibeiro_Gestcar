<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WashRequest;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Models\Wash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Registro das lavagens (B-09 / RF06 / UC06-HU01 e HU02).
 * A lavagem nasce de duas formas: de um agendamento concluido ou avulsa,
 * para o cliente que chega na hora sem ter agendado (D-14).
 */
class WashController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->input('from', today()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', today()->format('Y-m-d'));

        // Eager loading: sem ele, cada linha da tabela dispararia consultas
        // novas para o cliente e para o veiculo (problema do N+1).
        $washes = Wash::with(['user', 'vehicle'])
            ->whereBetween('performed_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderByDesc('performed_at')
            ->paginate(20)
            ->withQueryString();

        // O total vem de uma soma no banco, e nao da pagina atual, senao
        // mudaria a cada troca de pagina.
        $total = Wash::whereBetween('performed_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->sum('amount');

        return view('admin.washes.index', compact('washes', 'from', 'to', 'total'));
    }

    public function create(): View
    {
        $clients = User::where('role', 'cliente')
            ->notAnonymized()
            ->where('active', true)
            ->with('vehicles')
            ->orderBy('name')
            ->get();

        $services = Service::where('active', true)->orderBy('name')->get();

        return view('admin.washes.create', compact('clients', 'services'));
    }

    /**
     * Lavagem avulsa: atendimento sem agendamento (D-14).
     */
    public function store(WashRequest $request): RedirectResponse
    {
        $service = Service::findOrFail($request->service_id);

        $wash = Wash::fromService($service, [
            'user_id' => $request->user_id,
            'vehicle_id' => $request->vehicle_id,
            'amount' => $request->amount,
            'notes' => $request->notes,
            'performed_at' => $request->performed_at,
        ]);

        $wash->save();

        return redirect()->route('admin.washes.index')
            ->with('success', 'Lavagem avulsa registrada.');
    }

    public function storeForAppointment(WashRequest $request, Appointment $appointment): RedirectResponse
    {
        // Agendamento e intencao, lavagem e fato (D-14): so o atendimento
        // que de fato aconteceu entra no faturamento.
        if ($appointment->status !== 'concluido') {
            return redirect()->back()
                ->with('error', 'So e possivel registrar a lavagem de um agendamento concluido.');
        }

        // O `hasOne` da o limite de uma lavagem por agendamento. A checagem
        // aqui evita a lavagem duplicada por dois cliques ou pelo botao
        // voltar do navegador.
        if ($appointment->wash()->exists()) {
            return redirect()->back()
                ->with('error', 'Este agendamento ja tem lavagem registrada.');
        }

        $appointment->load('service');
        $appointment->registerWash($request->validated());

        return redirect()->back()->with('success', 'Lavagem registrada.');
    }
}

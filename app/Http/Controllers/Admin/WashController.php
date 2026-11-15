<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WashCorrectionRequest;
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
        // Mesmo cuidado da agenda: o periodo vem pela URL. Data invalida
        // cai no padrao, em vez de devolver uma lista vazia sem explicacao.
        $from = $this->dateOrDefault($request->input('from'), today()->startOfMonth());
        $to = $this->dateOrDefault($request->input('to'), today());

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

    private function dateOrDefault(?string $valor, \Carbon\Carbon $padrao): string
    {
        if (!$valor) {
            return $padrao->format('Y-m-d');
        }

        try {
            return \Carbon\Carbon::parse($valor)->format('Y-m-d');
        } catch (\Throwable $e) {
            return $padrao->format('Y-m-d');
        }
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

    public function edit(Wash $wash): View
    {
        $wash->load(['user', 'vehicle']);

        return view('admin.washes.edit', compact('wash'));
    }

    /**
     * Correcao: so valor e observacoes mudam. O `validated()` do
     * WashCorrectionRequest ja devolve apenas esses dois campos, entao
     * nada mais pode ser alterado nem forjando o formulario.
     */
    public function update(WashCorrectionRequest $request, Wash $wash): RedirectResponse
    {
        $wash->update($request->validated());

        return redirect()->route('admin.washes.index')
            ->with('success', 'Lavagem corrigida.');
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

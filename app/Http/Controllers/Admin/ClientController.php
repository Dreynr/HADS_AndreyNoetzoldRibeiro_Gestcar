<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClientRequest;
use App\Models\ClientNote;
use App\Models\FollowUp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::where('role', 'cliente')->notAnonymized();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Conta as lavagens, nao os agendamentos concluidos (D-18): e o
        // mesmo numero que o `visitsCount()` usa na ficha do cliente.
        // O `withMax` traz junto a data da ultima visita, que o segment()
        // precisa — as duas evitam uma consulta por linha da tabela.
        $query->withCount('washes as completed_count')
            ->withCount('washes')
            ->withMax('washes', 'performed_at')
            ->orderBy('name');

        if ($segment = $request->input('segment')) {
            // O segmento nao e uma coluna: ele e calculado a partir das
            // visitas e da data da ultima (RF09). Por isso o filtro acontece
            // na colecao, e a pagina e montada a mao depois.
            $clients = $this->paginateSegment($query, $segment, $request);
        } else {
            $clients = $query->paginate(15)->withQueryString();
        }

        $pendingFollowUps = FollowUp::where('completed', false)
            ->where('due_date', '<=', today())
            ->with('client')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        return view('admin.clients.index', compact('clients', 'pendingFollowUps'));
    }

    /**
     * Pagina uma lista filtrada por segmento (RF09 / UC09-HU01).
     *
     * Como o segmento e calculado em PHP, nao da para filtrar no banco nem
     * deixar o paginate() do Eloquent contar as linhas. Entao a lista inteira
     * e carregada, filtrada, e so a fatia da pagina atual vira resultado.
     * A base de clientes de uma estetica cabe nisso com folga.
     */
    private function paginateSegment($query, string $segment, Request $request): LengthAwarePaginator
    {
        $porPagina = 15;
        $pagina = $request->integer('page', 1) ?: 1;

        $filtrados = $query->get()->filter(
            fn (User $cliente) => $cliente->segment() === $segment
        )->values();

        return new LengthAwarePaginator(
            $filtrados->forPage($pagina, $porPagina),
            $filtrados->count(),
            $porPagina,
            $pagina,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /**
     * Painel de clientes inativos (B-11 / RF09 / UC09).
     *
     * Inativo e quem passou de noventa dias sem atendimento — a mesma regra
     * do `segment()`, para a tela nunca discordar do rotulo mostrado na
     * listagem de clientes.
     *
     * Quem nunca foi atendido nao entra: sem visita nenhuma nao existe
     * "tempo sem vir", e pelo `segment()` esse cliente e novo, nao inativo.
     */
    public function inactive(): View
    {
        $clients = User::where('role', 'cliente')
            ->notAnonymized()
            ->where('active', true)
            ->withCount('washes')
            ->withMax('washes', 'performed_at')
            ->get()
            ->filter(fn (User $cliente) => $cliente->segment() === 'inativo')
            // Mais tempo sem vir primeiro: e quem precisa ser chamado antes.
            ->sortByDesc(fn (User $cliente) => $cliente->daysSinceLastVisit())
            ->values();

        return view('admin.clients.inactive', compact('clients'));
    }

    public function create(): View
    {
        return view('admin.clients.create');
    }

    public function store(ClientRequest $request): RedirectResponse
    {
        $client = new User([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'birthday' => $request->birthday,
            'preferred_contact' => $request->preferred_contact,
        ]);
        $client->role = 'cliente';
        $client->save();

        return redirect()->route('admin.clients.index')
            ->with('success', 'Cliente cadastrado com sucesso.');
    }

    public function show(Request $request, User $client): View
    {
        $client->load(['vehicles', 'clientNotes.admin', 'followUps']);

        // Historico de lavagens do cliente, com filtro por veiculo e por
        // periodo (UC06-HU03). E o historico do que foi atendido de fato,
        // incluindo as lavagens avulsas.
        $washQuery = $client->washes()->with('vehicle')->orderByDesc('performed_at');

        if ($vehicleId = $request->input('wash_vehicle')) {
            $washQuery->where('vehicle_id', $vehicleId);
        }
        if ($washFrom = $request->input('wash_from')) {
            $washQuery->where('performed_at', '>=', $washFrom . ' 00:00:00');
        }
        if ($washTo = $request->input('wash_to')) {
            $washQuery->where('performed_at', '<=', $washTo . ' 23:59:59');
        }

        $washes = $washQuery->get();

        $appointments = $client->appointments()
            ->with(['service', 'vehicle'])
            ->latest('scheduled_at')
            ->get();

        $stats = [
            'total_spent' => $client->totalSpent(),
            'completed' => $client->visitsCount(),
            'total' => $appointments->count(),
            'cancelled' => $appointments->where('status', 'cancelado')->count(),
            'segment' => $client->segment(),
            'days_since_last' => $client->daysSinceLastVisit(),
            'last_date' => $client->lastVisitDate(),
        ];

        // Conta as lavagens, e nao os agendamentos concluidos (D-18): a
        // lavagem avulsa tambem e atendimento, e o nome vem da copia gravada
        // na lavagem (D-17), para servico renomeado nao sumir do historico.
        $topServices = $client->washes()
            ->selectRaw('service_name, COUNT(*) as total')
            ->groupBy('service_name')
            ->orderByDesc('total')
            ->limit(3)
            ->get()
            ->map(fn ($linha) => [
                'name' => $linha->service_name,
                'count' => (int) $linha->total,
            ]);

        $pendingFollowUps = $client->followUps()->where('completed', false)->orderBy('due_date')->get();
        $completedFollowUps = $client->followUps()->where('completed', true)->latest('completed_at')->limit(5)->get();

        return view('admin.clients.show', compact(
            'client', 'appointments', 'stats', 'topServices', 'pendingFollowUps', 'completedFollowUps',
            'washes', 'vehicleId', 'washFrom', 'washTo'
        ));
    }

    public function edit(User $client): View
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(ClientRequest $request, User $client): RedirectResponse
    {
        $data = [
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'birthday' => $request->birthday,
            'preferred_contact' => $request->preferred_contact,
            'preferences' => $request->preferences,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $client->update($data);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Cliente atualizado com sucesso.');
    }

    public function destroy(User $client): RedirectResponse
    {
        // Lavagem tambem e historico: cliente que foi atendido alguma vez
        // nunca e excluido, porque o atendimento entra no fechamento mensal.
        $hasHistory = $client->appointments()->exists() || $client->washes()->exists();

        if ($hasHistory) {
            $client->update(['active' => false]);

            return redirect()->route('admin.clients.index')
                ->with('success', 'Cliente inativado (possui historico de atendimentos).');
        }

        $client->delete();

        return redirect()->route('admin.clients.index')
            ->with('success', 'Cliente excluido com sucesso.');
    }

    public function toggleActive(User $client): RedirectResponse
    {
        $client->update(['active' => ! $client->active]);
        $status = $client->active ? 'ativado' : 'inativado';

        return redirect()->route('admin.clients.index')
            ->with('success', "Cliente {$status} com sucesso.");
    }

    public function storeNote(Request $request, User $client): RedirectResponse
    {
        $request->validate(['content' => 'required|string|max:1000']);

        ClientNote::create([
            'client_id' => $client->id,
            'admin_id' => auth()->id(),
            'content' => $request->content,
        ]);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Nota adicionada.');
    }

    public function destroyNote(User $client, ClientNote $note): RedirectResponse
    {
        // A nota precisa ser deste cliente. Sem esta checagem, trocar o id na
        // URL apagaria a nota de outra pessoa: a rota diz "nota deste
        // cliente", mas o Laravel so carrega os dois registros separados e
        // nunca confere se um pertence ao outro.
        abort_unless($note->client_id === $client->id, 404);

        $note->delete();

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Nota removida.');
    }

    public function storeFollowUp(Request $request, User $client): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'due_date' => 'required|date|after_or_equal:today',
            'type' => 'required|in:retorno,aniversario,personalizado',
        ]);

        // Dois cliques no botao criavam dois follow-ups iguais. Se ja existe
        // um em aberto com o mesmo tipo e titulo para este cliente, o segundo
        // nao e lembrete novo — e o mesmo lembrete duas vezes.
        $jaExiste = $client->followUps()
            ->where('completed', false)
            ->where('type', $request->type)
            ->where('title', $request->title)
            ->exists();

        if ($jaExiste) {
            return redirect()->back()
                ->with('error', 'Este cliente ja tem um follow-up igual em aberto.');
        }

        FollowUp::create([
            'client_id' => $client->id,
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
        ]);

        // Volta para a tela de onde o follow-up foi criado: a ficha do
        // cliente ou o painel de inativos (B-11).
        return redirect()->back()
            ->with('success', 'Follow-up criado.');
    }

    public function completeFollowUp(User $client, FollowUp $followUp): RedirectResponse
    {
        // Mesma checagem da nota: o follow-up tem que ser deste cliente.
        abort_unless($followUp->client_id === $client->id, 404);

        $followUp->update([
            'completed' => true,
            'completed_at' => now(),
        ]);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Follow-up concluido.');
    }
}

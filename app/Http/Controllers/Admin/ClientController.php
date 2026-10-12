<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClientRequest;
use App\Models\ClientNote;
use App\Models\FollowUp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::where('role', 'cliente');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($segment = $request->input('segment')) {
            $query->get()->filter(fn ($c) => $c->segment() === $segment);
        }

        $clients = $query->withCount(['appointments as completed_count' => function ($q) {
            $q->where('status', 'concluido');
        }])->orderBy('name')->paginate(15)->withQueryString();

        $pendingFollowUps = FollowUp::where('completed', false)
            ->where('due_date', '<=', today())
            ->with('client')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        return view('admin.clients.index', compact('clients', 'pendingFollowUps'));
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

    public function show(User $client): View
    {
        $client->load(['vehicles', 'clientNotes.admin', 'followUps']);

        $appointments = $client->appointments()
            ->with(['service', 'vehicle'])
            ->latest('scheduled_at')
            ->get();

        $stats = [
            'total_spent' => $client->totalSpent(),
            'completed' => $client->completedAppointmentsCount(),
            'total' => $appointments->count(),
            'cancelled' => $appointments->where('status', 'cancelado')->count(),
            'segment' => $client->segment(),
            'days_since_last' => $client->daysSinceLastVisit(),
            'last_date' => $client->lastAppointmentDate(),
        ];

        $topServices = $appointments->where('status', 'concluido')
            ->groupBy('service_id')
            ->map(fn ($group) => [
                'name' => $group->first()->service->name,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->take(3)
            ->values();

        $pendingFollowUps = $client->followUps()->where('completed', false)->orderBy('due_date')->get();
        $completedFollowUps = $client->followUps()->where('completed', true)->latest('completed_at')->limit(5)->get();

        return view('admin.clients.show', compact(
            'client', 'appointments', 'stats', 'topServices', 'pendingFollowUps', 'completedFollowUps'
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
        $hasAppointments = $client->appointments()->exists();

        if ($hasAppointments) {
            $client->update(['active' => false]);

            return redirect()->route('admin.clients.index')
                ->with('success', 'Cliente inativado (possui agendamentos vinculados).');
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

        FollowUp::create([
            'client_id' => $client->id,
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
        ]);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Follow-up criado.');
    }

    public function completeFollowUp(User $client, FollowUp $followUp): RedirectResponse
    {
        $followUp->update([
            'completed' => true,
            'completed_at' => now(),
        ]);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Follow-up concluido.');
    }
}

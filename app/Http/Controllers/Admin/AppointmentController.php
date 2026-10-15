<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentStatusMail;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $view = $request->input('view', 'day');
        $date = $request->input('date', today()->format('Y-m-d'));
        $baseDate = Carbon::parse($date);

        switch ($view) {
            case 'week':
                $start = $baseDate->copy()->startOfWeek();
                $end = $baseDate->copy()->endOfWeek()->endOfDay();
                break;
            case 'month':
                $start = $baseDate->copy()->startOfMonth();
                $end = $baseDate->copy()->endOfMonth()->endOfDay();
                break;
            default:
                $view = 'day';
                $start = $baseDate->copy()->startOfDay();
                $end = $baseDate->copy()->endOfDay();
        }

        $query = Appointment::with(['user', 'vehicle', 'service', 'extras'])
            ->whereBetween('scheduled_at', [$start, $end])
            ->orderBy('scheduled_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $appointments = $query->get();

        $stats = [
            'total' => $appointments->count(),
            'pendente' => $appointments->where('status', 'pendente')->count(),
            'confirmado' => $appointments->where('status', 'confirmado')->count(),
            'concluido' => $appointments->where('status', 'concluido')->count(),
            'cancelado' => $appointments->where('status', 'cancelado')->count(),
        ];

        return view('admin.appointments.index', compact(
            'appointments', 'view', 'date', 'baseDate', 'start', 'end', 'stats'
        ));
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:pendente,confirmado,concluido,cancelado'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!$appointment->canTransitionTo($request->status)) {
            return redirect()->back()
                ->with('error', "Não é possível mudar de \"{$appointment->status}\" para \"{$request->status}\".");
        }

        $data = ['status' => $request->status];
        if ($request->filled('admin_notes')) {
            $data['admin_notes'] = $request->admin_notes;
        }

        $appointment->update($data);

        if (in_array($request->status, ['confirmado', 'cancelado', 'concluido'])) {
            $appointment->load(['user', 'vehicle', 'service', 'extras']);
            try {
                Mail::to($appointment->user->email)
                    ->send(new AppointmentStatusMail($appointment, $request->status, $request->admin_notes));
            } catch (\Exception $e) {
                report($e);
            }
        }

        return redirect()->back()
            ->with('success', "Agendamento marcado como {$request->status}.");
    }

    public function show(Appointment $appointment): View
    {
        $appointment->load(['user', 'vehicle', 'service', 'extras']);

        return view('admin.appointments.show', compact('appointment'));
    }
}

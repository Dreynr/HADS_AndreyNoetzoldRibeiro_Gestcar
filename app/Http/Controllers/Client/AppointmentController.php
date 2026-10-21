<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\AppointmentRequest;
use App\Mail\AppointmentBookedAdminMail;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(): View
    {
        $appointments = auth()->user()->appointments()
            ->with(['vehicle', 'service', 'extras'])
            ->whereIn('status', ['pendente', 'confirmado'])
            ->orderBy('scheduled_at')
            ->get();

        return view('client.appointments.index', compact('appointments'));
    }

    public function create(): View
    {
        $vehicles = auth()->user()->vehicles()->orderBy('model')->get();
        $principalServices = Service::where('active', true)->principal()->orderBy('name')->get();
        $extraServices = Service::where('active', true)->extra()->orderBy('name')->get();

        return view('client.appointments.create', compact('vehicles', 'principalServices', 'extraServices'));
    }

    public function store(AppointmentRequest $request): RedirectResponse
    {
        $vehicle = auth()->user()->vehicles()->findOrFail($request->vehicle_id);
        $service = Service::where('active', true)->principal()->findOrFail($request->service_id);

        // Carregados antes da checagem de vaga, porque um extra tambem
        // pode exigir o periodo inteiro (D-13: o preco e o de agora).
        $extras = Service::where('active', true)
            ->extra()
            ->whereIn('id', $request->extras ?? [])
            ->get();

        $date = $request->date;
        $period = $request->period;
        $carbon = Carbon::parse($date);

        if ($carbon->lte(today())) {
            return back()->withInput()->withErrors(['date' => 'A data deve ser futura.']);
        }

        $isWeekend = $carbon->isWeekend();
        $validPeriods = $isWeekend ? ['manha', 'tarde', 'noite'] : ['noite'];

        if (!in_array($period, $validPeriods)) {
            return back()->withInput()->withErrors(['period' => 'Periodo indisponivel para este dia.']);
        }

        $check = Appointment::canBook(
            $date,
            $period,
            Appointment::anyBlocksPeriod($extras->concat([$service]))
        );
        if (!$check['ok']) {
            return back()->withInput()->withErrors(['period' => $check['error']]);
        }

        $scheduledAt = $carbon->copy()->setTime(Appointment::periodStart($period), 0);

        $appointment = Appointment::create([
            'user_id' => auth()->id(),
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'scheduled_at' => $scheduledAt,
            'period' => $period,
            'status' => 'pendente',
            'notes' => $request->notes,
        ]);

        foreach ($extras as $extra) {
            $appointment->extras()->attach($extra->id, ['price' => $extra->price]);
        }

        $appointment->load(['user', 'vehicle', 'service', 'extras']);

        $admins = User::where('role', 'admin')->pluck('email');
        foreach ($admins as $adminEmail) {
            try {
                Mail::to($adminEmail)->send(new AppointmentBookedAdminMail($appointment));
            } catch (\Exception $e) {
                report($e);
            }
        }

        return redirect()->route('dashboard')
            ->with('success', 'Agendamento realizado com sucesso!');
    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->user_id === auth()->id(), 403);

        if (!in_array($appointment->status, ['pendente', 'confirmado'])) {
            return redirect()->route('appointments.index')
                ->with('error', 'Este agendamento nao pode ser cancelado.');
        }

        $appointment->update(['status' => 'cancelado']);

        return redirect()->route('appointments.index')
            ->with('success', 'Agendamento cancelado com sucesso.');
    }

    public function history(): View
    {
        $appointments = auth()->user()->appointments()
            ->with(['vehicle', 'service', 'extras'])
            ->orderByDesc('scheduled_at')
            ->paginate(15);

        return view('client.history.index', compact('appointments'));
    }

    public function bookedDates(Request $request): JsonResponse
    {
        $request->validate([
            'year' => ['required', 'integer', 'min:2026'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $year = $request->year;
        $month = $request->month;
        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();
        $todayStr = today()->format('Y-m-d');

        $bookedDates = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');

            if ($dateStr <= $todayStr) {
                continue;
            }

            $isWeekend = $date->isWeekend();
            $periods = $isWeekend ? ['manha', 'tarde', 'noite'] : ['noite'];

            $allFull = true;
            foreach ($periods as $p) {
                $avail = Appointment::periodAvailability($dateStr, $p);
                if ($avail['available']) {
                    $allFull = false;
                    break;
                }
            }

            if ($allFull) {
                $bookedDates[] = $dateStr;
            }
        }

        return response()->json($bookedDates);
    }

    public function availablePeriods(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date', 'after:today'],
            'service_id' => ['nullable', 'exists:services,id'],
            'extras' => ['nullable', 'array'],
            'extras.*' => ['exists:services,id'],
        ]);

        $date = $request->date;
        $carbon = Carbon::parse($date);
        $isWeekend = $carbon->isWeekend();

        $service = $request->service_id
            ? Service::find($request->service_id)
            : null;

        // A tela precisa aplicar a mesma regra do store(): um extra tambem
        // pode exigir o periodo inteiro e fechar o periodo.
        $extras = Service::where('active', true)
            ->extra()
            ->whereIn('id', $request->extras ?? [])
            ->get();

        $periodKeys = $isWeekend ? ['manha', 'tarde', 'noite'] : ['noite'];

        $periods = [];
        foreach ($periodKeys as $p) {
            $avail = Appointment::periodAvailability($date, $p);

            if ($service && $avail['available']) {
                $check = Appointment::canBook(
                    $date,
                    $p,
                    Appointment::anyBlocksPeriod($extras->concat([$service]))
                );
                $avail['available'] = $check['ok'];
                if (!$check['ok']) {
                    $avail['reason'] = $check['error'];
                }
            }

            $periods[] = [
                'key' => $p,
                'label' => Appointment::periodLabel($p),
                'available' => $avail['available'],
                'remaining' => $avail['remaining'] ?? 0,
                'reason' => $avail['reason'] ?? null,
            ];
        }

        return response()->json($periods);
    }
}

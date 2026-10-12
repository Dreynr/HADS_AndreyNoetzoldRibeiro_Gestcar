<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $upcomingAppointments = Appointment::with(['user', 'vehicle', 'service', 'extras'])
                ->whereIn('status', ['pendente', 'confirmado'])
                ->where('scheduled_at', '>=', today())
                ->orderBy('scheduled_at')
                ->get();

            $appointmentsByDate = $upcomingAppointments->groupBy(fn ($a) => $a->scheduled_at->format('Y-m-d'));

            $calendarData = $appointmentsByDate->mapWithKeys(fn ($items, $date) => [
                $date => $items->map(fn ($a) => [
                    'id' => $a->id,
                    'service' => $a->service->name,
                    'client' => $a->user->name,
                    'vehicle' => $a->vehicle->model . ' - ' . $a->vehicle->plate,
                    'period' => Appointment::periodLabel($a->period),
                    'status' => $a->status,
                    'url' => route('admin.appointments.show', $a->id),
                ])->values()
            ]);

            $stats = [
                'pendente' => $upcomingAppointments->where('status', 'pendente')->count(),
                'confirmado' => $upcomingAppointments->where('status', 'confirmado')->count(),
                'hoje' => $upcomingAppointments->filter(fn ($a) => $a->scheduled_at->isToday())->count(),
            ];

            return view('admin.dashboard', compact('upcomingAppointments', 'calendarData', 'stats'));
        }

        $vehicles = $user->vehicles()->orderBy('model')->get();
        $principalServices = Service::where('active', true)->principal()->orderBy('name')->get();
        $extraServices = Service::where('active', true)->extra()->orderBy('name')->get();

        $upcomingAppointments = $user->appointments()
            ->with(['vehicle', 'service'])
            ->whereIn('status', ['pendente', 'confirmado'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        return view('client.dashboard', compact('vehicles', 'principalServices', 'extraServices', 'upcomingAppointments'));
    }
}

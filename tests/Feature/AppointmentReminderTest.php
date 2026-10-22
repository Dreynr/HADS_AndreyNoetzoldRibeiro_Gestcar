<?php

namespace Tests\Feature;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * B-04 / RF10 / UC10-HU03 / D-06 — os lembretes vao para agendamentos
 * pendentes e confirmados, e cada um e enviado uma unica vez.
 */
class AppointmentReminderTest extends TestCase
{
    use RefreshDatabase;

    private function appointmentFor(string $status, string $date): Appointment
    {
        $user = User::factory()->create();

        $vehicle = Vehicle::create([
            'user_id' => $user->id,
            'plate' => 'ABC1D23',
            'model' => 'Fusca',
            'color' => 'Azul',
        ]);

        $service = Service::create([
            'name' => 'Lavagem Externa Simples',
            'type' => 'principal',
            'price' => 40.00,
            'duration_minutes' => 45,
            'blocks_period' => false,
        ]);

        return Appointment::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'scheduled_at' => $date . ' 17:00:00',
            'period' => 'noite',
            'status' => $status,
        ]);
    }

    public function test_agendamento_pendente_para_amanha_recebe_o_lembrete_de_um_dia(): void
    {
        Mail::fake();

        $appointment = $this->appointmentFor('pendente', now()->addDay()->format('Y-m-d'));

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Mail::assertSent(AppointmentReminderMail::class);
        $this->assertTrue((bool) $appointment->fresh()->reminder_1day_sent);
    }

    public function test_agendamento_confirmado_para_amanha_recebe_o_lembrete_de_um_dia(): void
    {
        Mail::fake();

        $appointment = $this->appointmentFor('confirmado', now()->addDay()->format('Y-m-d'));

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Mail::assertSent(AppointmentReminderMail::class);
        $this->assertTrue((bool) $appointment->fresh()->reminder_1day_sent);
    }

    public function test_agendamento_cancelado_nao_recebe_lembrete(): void
    {
        Mail::fake();

        $appointment = $this->appointmentFor('cancelado', now()->addDay()->format('Y-m-d'));

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertFalse((bool) $appointment->fresh()->reminder_1day_sent);
    }

    public function test_o_mesmo_lembrete_nao_e_enviado_duas_vezes(): void
    {
        Mail::fake();

        $this->appointmentFor('pendente', now()->addDay()->format('Y-m-d'));

        $this->artisan('appointments:send-reminders')->assertSuccessful();
        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Mail::assertSentCount(1);
    }

    public function test_lembrete_de_uma_hora_ignora_agendamentos_de_outros_dias(): void
    {
        Mail::fake();

        // Daqui a um mes: nunca deve entrar na consulta do lembrete de 1 hora.
        $appointment = $this->appointmentFor('confirmado', now()->addMonth()->format('Y-m-d'));

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        $this->assertFalse((bool) $appointment->fresh()->reminder_1hour_sent);
    }
}

<?php

namespace Tests\Feature;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * B-04 / RF10 / UC10-HU03 / D-06 / D-22 — os lembretes vao para agendamentos
 * pendentes e confirmados, cada um e enviado uma unica vez, e as duas janelas
 * sao contadas a partir do HORARIO marcado, nao do dia nem do inicio do
 * periodo.
 */
class AppointmentReminderTest extends TestCase
{
    use RefreshDatabase;

    private function appointmentAt(Carbon $quando, string $status = 'confirmado', string $period = 'noite'): Appointment
    {
        $user = User::factory()->create();

        $vehicle = Vehicle::create([
            'user_id' => $user->id,
            'plate' => 'ABC1D23',
            'model' => 'Fusca',
            'color' => 'Azul',
        ]);

        $service = Service::create([
            'name' => 'Lavagem Externa Simples ' . $user->id,
            'type' => 'principal',
            'price' => 40.00,
            'duration_minutes' => 45,
            'blocks_period' => false,
        ]);

        return Appointment::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'scheduled_at' => $quando,
            'period' => $period,
            'status' => $status,
        ]);
    }

    public function test_agendamento_pendente_dentro_de_24_horas_recebe_o_lembrete_de_um_dia(): void
    {
        Mail::fake();

        $appointment = $this->appointmentAt(now()->addHours(20), 'pendente');

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Mail::assertSent(AppointmentReminderMail::class);
        $this->assertEquals(1, $appointment->fresh()->reminder_1day_sent);
    }

    public function test_agendamento_confirmado_dentro_de_24_horas_recebe_o_lembrete_de_um_dia(): void
    {
        Mail::fake();

        $appointment = $this->appointmentAt(now()->addHours(20), 'confirmado');

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Mail::assertSent(AppointmentReminderMail::class);
        $this->assertEquals(1, $appointment->fresh()->reminder_1day_sent);
    }

    /**
     * O lembrete e "um dia antes", entao um agendamento daqui a tres dias
     * ainda nao e hora de avisar.
     */
    public function test_agendamento_distante_ainda_nao_recebe_o_lembrete(): void
    {
        Mail::fake();

        $appointment = $this->appointmentAt(now()->addDays(3));

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertEquals(0, $appointment->fresh()->reminder_1day_sent);
    }

    public function test_agendamento_cancelado_nao_recebe_lembrete(): void
    {
        Mail::fake();

        $appointment = $this->appointmentAt(now()->addHours(20), 'cancelado');

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertEquals(0, $appointment->fresh()->reminder_1day_sent);
    }

    public function test_o_mesmo_lembrete_nao_e_enviado_duas_vezes(): void
    {
        Mail::fake();

        $this->appointmentAt(now()->addHours(20), 'pendente');

        $this->artisan('appointments:send-reminders')->assertSuccessful();
        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Mail::assertSentCount(1);
    }

    public function test_lembrete_de_uma_hora_sai_quando_falta_menos_de_uma_hora(): void
    {
        Mail::fake();

        $appointment = $this->appointmentAt(now()->addMinutes(30));

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        $this->assertEquals(1, $appointment->fresh()->reminder_1hour_sent);
    }

    public function test_lembrete_de_uma_hora_ignora_agendamento_de_outro_dia(): void
    {
        Mail::fake();

        $appointment = $this->appointmentAt(now()->addMonth());

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        $this->assertEquals(0, $appointment->fresh()->reminder_1hour_sent);
    }

    /**
     * O teste que protege a correcao da D-22.
     *
     * Dois agendamentos no MESMO periodo da noite, em horarios diferentes:
     * as 17:00 e as 19:30. As 16:10, so o das 17:00 esta a menos de uma hora
     * de comecar. Antes da D-22 o lembrete usava o inicio do periodo, entao
     * os dois clientes eram avisados as 16h — e quem marcou 19:30 recebia o
     * aviso tres horas e meia adiantado.
     */
    public function test_lembrete_de_uma_hora_respeita_o_horario_escolhido_dentro_do_periodo(): void
    {
        Mail::fake();

        $this->travelTo(Carbon::parse('2026-11-26 16:10:00'));

        $cedo = $this->appointmentAt(Carbon::parse('2026-11-26 17:00:00'));
        $tarde = $this->appointmentAt(Carbon::parse('2026-11-26 19:30:00'));

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        $this->assertEquals(1, $cedo->fresh()->reminder_1hour_sent, 'O das 17:00 devia ter recebido.');
        $this->assertEquals(0, $tarde->fresh()->reminder_1hour_sent, 'O das 19:30 ainda nao devia receber.');

        // Tres horas depois, as 18:40, agora e a vez do das 19:30.
        $this->travelTo(Carbon::parse('2026-11-26 18:40:00'));
        $this->artisan('appointments:send-reminders')->assertSuccessful();

        $this->assertEquals(1, $tarde->fresh()->reminder_1hour_sent);

        $this->travelBack();
    }
}

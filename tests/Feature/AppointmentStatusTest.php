<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * B-07 item 8 / B-03 / RF05 — cancelado e concluido sao finais.
 * O mapa de transicoes vive em Appointment::ALLOWED_TRANSITIONS.
 */
class AppointmentStatusTest extends TestCase
{
    use RefreshDatabase;

    private function updateStatus(Appointment $appointment, string $status)
    {
        $admin = User::factory()->create(['role' => 'admin']);

        return $this->actingAs($admin)->patch(
            route('admin.appointments.update-status', $appointment),
            ['status' => $status]
        );
    }

    public function test_cancelado_nao_pode_virar_concluido(): void
    {
        Mail::fake();

        $appointment = Appointment::factory()->status('cancelado')->create();

        $this->updateStatus($appointment, 'concluido')->assertSessionHas('error');

        $this->assertSame('cancelado', $appointment->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_concluido_nao_pode_voltar_para_pendente(): void
    {
        Mail::fake();

        $appointment = Appointment::factory()->status('concluido')->create();

        $this->updateStatus($appointment, 'pendente')->assertSessionHas('error');

        $this->assertSame('concluido', $appointment->fresh()->status);
    }

    public function test_pendente_pode_virar_confirmado(): void
    {
        Mail::fake();

        $appointment = Appointment::factory()->status('pendente')->create();

        $this->updateStatus($appointment, 'confirmado');

        $this->assertSame('confirmado', $appointment->fresh()->status);
    }

    public function test_confirmado_pode_virar_concluido(): void
    {
        Mail::fake();

        $appointment = Appointment::factory()->status('confirmado')->create();

        $this->updateStatus($appointment, 'concluido');

        $this->assertSame('concluido', $appointment->fresh()->status);
    }

    public function test_pendente_nao_pula_direto_para_concluido(): void
    {
        Mail::fake();

        $appointment = Appointment::factory()->status('pendente')->create();

        $this->updateStatus($appointment, 'concluido')->assertSessionHas('error');

        $this->assertSame('pendente', $appointment->fresh()->status);
    }
}

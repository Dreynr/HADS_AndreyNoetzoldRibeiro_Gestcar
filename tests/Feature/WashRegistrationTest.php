<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * B-09 / RF06 / UC06-HU01 — o admin registra a lavagem do agendamento
 * concluido, com o valor sugerido pelo sistema e editavel (D-13).
 */
class WashRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function appointment(string $status = 'concluido'): Appointment
    {
        $client = User::factory()->create(['role' => 'cliente']);

        return Appointment::factory()->create([
            'user_id' => $client->id,
            'vehicle_id' => Vehicle::factory()->create(['user_id' => $client->id])->id,
            'service_id' => Service::factory()->create(['name' => 'Lavagem Completa', 'price' => 80.00])->id,
            'scheduled_at' => now()->subDay()->setTime(17, 0),
            'status' => $status,
        ]);
    }

    public function test_admin_registra_a_lavagem_de_um_agendamento_concluido(): void
    {
        $appointment = $this->appointment();

        $this->actingAs($this->admin)
            ->post(route('admin.washes.store-for-appointment', $appointment), [
                'amount' => 80.00,
                'performed_at' => now()->subDay()->format('Y-m-d'),
                'notes' => 'Carro muito sujo, levou mais tempo.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('washes', [
            'appointment_id' => $appointment->id,
            'user_id' => $appointment->user_id,
            'vehicle_id' => $appointment->vehicle_id,
            'service_id' => $appointment->service_id,
            // D-17: o nome fica gravado na lavagem, nao buscado na hora.
            'service_name' => 'Lavagem Completa',
            'amount' => 80.00,
        ]);
    }

    public function test_valor_cobrado_pode_ser_diferente_do_preco_do_servico(): void
    {
        $appointment = $this->appointment();

        // Desconto dado na hora pelo admin.
        $this->actingAs($this->admin)
            ->post(route('admin.washes.store-for-appointment', $appointment), [
                'amount' => 65.00,
                'performed_at' => now()->subDay()->format('Y-m-d'),
            ]);

        $this->assertDatabaseHas('washes', ['appointment_id' => $appointment->id, 'amount' => 65.00]);
        // O preco do servico nao e alterado pelo desconto.
        $this->assertDatabaseHas('services', ['id' => $appointment->service_id, 'price' => 80.00]);
    }

    public function test_nao_registra_lavagem_de_agendamento_nao_concluido(): void
    {
        $appointment = $this->appointment('confirmado');

        $this->actingAs($this->admin)
            ->post(route('admin.washes.store-for-appointment', $appointment), [
                'amount' => 80.00,
                'performed_at' => today()->format('Y-m-d'),
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('washes', 0);
    }

    public function test_nao_registra_duas_lavagens_para_o_mesmo_agendamento(): void
    {
        $appointment = $this->appointment();
        $dados = ['amount' => 80.00, 'performed_at' => today()->format('Y-m-d')];

        $this->actingAs($this->admin)->post(route('admin.washes.store-for-appointment', $appointment), $dados);
        $this->actingAs($this->admin)->post(route('admin.washes.store-for-appointment', $appointment), $dados)
            ->assertSessionHas('error');

        $this->assertDatabaseCount('washes', 1);
    }

    public function test_data_no_futuro_e_recusada(): void
    {
        $appointment = $this->appointment();

        $this->actingAs($this->admin)
            ->post(route('admin.washes.store-for-appointment', $appointment), [
                'amount' => 80.00,
                'performed_at' => today()->addWeek()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('performed_at');

        $this->assertDatabaseCount('washes', 0);
    }

    public function test_valor_negativo_e_recusado(): void
    {
        $appointment = $this->appointment();

        $this->actingAs($this->admin)
            ->post(route('admin.washes.store-for-appointment', $appointment), [
                'amount' => -10,
                'performed_at' => today()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('washes', 0);
    }

    public function test_cliente_nao_pode_registrar_lavagem(): void
    {
        $appointment = $this->appointment();

        $this->actingAs($appointment->user)
            ->post(route('admin.washes.store-for-appointment', $appointment), [
                'amount' => 1.00,
                'performed_at' => today()->format('Y-m-d'),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('washes', 0);
    }

    public function test_a_tela_do_agendamento_mostra_o_formulario_e_depois_a_lavagem(): void
    {
        $appointment = $this->appointment();

        $this->actingAs($this->admin)
            ->get(route('admin.appointments.show', $appointment))
            ->assertSee('Registrar lavagem');

        $appointment->registerWash([
            'amount' => 80.00,
            'performed_at' => now()->subDay()->format('Y-m-d'),
            'notes' => null,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.appointments.show', $appointment))
            ->assertDontSee('Registrar lavagem')
            ->assertSee('Lavagem Completa');
    }
}

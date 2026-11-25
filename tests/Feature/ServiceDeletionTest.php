<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * UC04-HU04 — servico com historico e inativado, nunca excluido.
 *
 * Um servico e usado de DUAS formas: como principal, em
 * `appointments.service_id`, e como extra, em `appointment_extras`. O teste
 * de aceitacao de 25/11/2026 encontrou um erro 500 ao excluir a "Aplicacao
 * de Cera": ela so tinha sido usada como extra, o sistema achou que nunca
 * fora usada, tentou excluir e esbarrou na chave estrangeira.
 */
class ServiceDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function appointmentWithExtra(Service $extra, string $status): Appointment
    {
        $client = User::factory()->create(['role' => 'cliente']);

        $appointment = Appointment::factory()->create([
            'user_id' => $client->id,
            'vehicle_id' => Vehicle::factory()->create(['user_id' => $client->id])->id,
            'service_id' => Service::factory()->create()->id,
            'status' => $status,
        ]);

        $appointment->extras()->attach($extra->id, ['price' => $extra->price]);

        return $appointment;
    }

    public function test_servico_sem_uso_nenhum_e_excluido(): void
    {
        $service = Service::factory()->create(['name' => 'Nunca Usado']);

        $this->actingAs($this->admin())
            ->delete(route('admin.services.destroy', $service))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    /**
     * O caso que dava erro 500.
     */
    public function test_servico_usado_so_como_extra_e_inativado_em_vez_de_excluido(): void
    {
        $extra = Service::factory()->extra()->create(['name' => 'Aplicacao de Cera']);

        $this->appointmentWithExtra($extra, 'concluido');

        $this->actingAs($this->admin())
            ->delete(route('admin.services.destroy', $extra))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('services', ['id' => $extra->id, 'active' => false]);
    }

    public function test_servico_extra_de_agendamento_pendente_nao_pode_ser_excluido(): void
    {
        $extra = Service::factory()->extra()->create(['name' => 'Cristalizacao']);

        $this->appointmentWithExtra($extra, 'pendente');

        $this->actingAs($this->admin())
            ->delete(route('admin.services.destroy', $extra))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('services', ['id' => $extra->id, 'active' => true]);
    }

    public function test_servico_principal_com_agendamento_pendente_nao_pode_ser_excluido(): void
    {
        $service = Service::factory()->create(['name' => 'Lavagem Simples']);
        $client = User::factory()->create(['role' => 'cliente']);

        Appointment::factory()->create([
            'user_id' => $client->id,
            'vehicle_id' => Vehicle::factory()->create(['user_id' => $client->id])->id,
            'service_id' => $service->id,
            'status' => 'pendente',
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.services.destroy', $service))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('services', ['id' => $service->id, 'active' => true]);
    }
}

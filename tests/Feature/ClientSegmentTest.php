<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * B-07 item 9 / RF09 — a segmentacao automatica do CRM.
 * A ordem das regras importa: inatividade vence a contagem de visitas.
 */
class ClientSegmentTest extends TestCase
{
    use RefreshDatabase;

    private function clientWith(int $completed, int $daysAgo): User
    {
        $client = User::factory()->create(['role' => 'cliente']);

        Appointment::factory()->count($completed)->status('concluido')->create([
            'user_id' => $client->id,
            'scheduled_at' => now()->subDays($daysAgo),
        ]);

        return $client;
    }

    public function test_cliente_sem_visitas_e_novo(): void
    {
        $client = User::factory()->create(['role' => 'cliente']);

        $this->assertSame('novo', $client->segment());
    }

    public function test_duas_visitas_ainda_e_novo(): void
    {
        $this->assertSame('novo', $this->clientWith(2, 10)->segment());
    }

    public function test_tres_visitas_vira_recorrente(): void
    {
        $this->assertSame('recorrente', $this->clientWith(3, 10)->segment());
    }

    public function test_dez_visitas_vira_vip(): void
    {
        $this->assertSame('vip', $this->clientWith(10, 10)->segment());
    }

    public function test_mais_de_noventa_dias_sem_vir_e_inativo(): void
    {
        $this->assertSame('inativo', $this->clientWith(3, 120)->segment());
    }

    public function test_inatividade_vence_a_contagem_de_visitas(): void
    {
        // Mesmo com visitas de VIP, sumir por mais de 90 dias manda para inativo.
        $this->assertSame('inativo', $this->clientWith(10, 120)->segment());
    }
}

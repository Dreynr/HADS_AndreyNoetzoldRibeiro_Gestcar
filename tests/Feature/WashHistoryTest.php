<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Wash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * B-09 / RF06 / UC06-HU03 — historico de lavagens na ficha do cliente,
 * com filtro por veiculo e por periodo. O total gasto passa a vir dos
 * valores gravados nas lavagens (D-13).
 */
class WashHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_total_gasto_soma_as_lavagens_e_nao_o_preco_atual_do_servico(): void
    {
        $client = User::factory()->create(['role' => 'cliente']);
        $vehicle = Vehicle::factory()->create(['user_id' => $client->id]);
        $service = Service::factory()->create(['price' => 50.00]);

        Wash::factory()->create([
            'user_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'amount' => 50.00,
        ]);

        // A tabela de precos e reajustada depois do atendimento.
        $service->update(['price' => 90.00]);

        $this->assertSame(50.0, $client->fresh()->totalSpent());
    }

    public function test_ficha_do_cliente_mostra_as_lavagens_mais_recentes_primeiro(): void
    {
        $client = User::factory()->create(['role' => 'cliente']);
        $vehicle = Vehicle::factory()->create(['user_id' => $client->id]);

        Wash::factory()->create([
            'user_id' => $client->id, 'vehicle_id' => $vehicle->id,
            'service_name' => 'Lavagem Antiga', 'performed_at' => '2026-09-01 10:00:00',
        ]);
        Wash::factory()->create([
            'user_id' => $client->id, 'vehicle_id' => $vehicle->id,
            'service_name' => 'Lavagem Recente', 'performed_at' => '2026-10-20 10:00:00',
        ]);

        $html = $this->actingAs($this->admin)
            ->get(route('admin.clients.show', $client))
            ->assertOk()
            ->getContent();

        $this->assertLessThan(
            strpos($html, 'Lavagem Antiga'),
            strpos($html, 'Lavagem Recente'),
            'A lavagem mais recente deve aparecer antes da mais antiga.'
        );
    }

    public function test_historico_filtra_por_veiculo(): void
    {
        $client = User::factory()->create(['role' => 'cliente']);
        $carro = Vehicle::factory()->create(['user_id' => $client->id, 'plate' => 'AAA1A11']);
        $moto = Vehicle::factory()->create(['user_id' => $client->id, 'plate' => 'BBB2B22']);

        // Mesmo nome de servico nas duas, e valores diferentes: o filtro e
        // conferido pelo valor, que so aparece na tabela do historico. O nome
        // do servico tambem aparece no quadro "Servicos mais utilizados", que
        // resume o cliente inteiro e de proposito nao segue o filtro.
        Wash::factory()->create([
            'user_id' => $client->id, 'vehicle_id' => $carro->id,
            'service_name' => 'Lavagem simples', 'amount' => 111.00,
        ]);
        Wash::factory()->create([
            'user_id' => $client->id, 'vehicle_id' => $moto->id,
            'service_name' => 'Lavagem simples', 'amount' => 222.00,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.clients.show', [$client, 'wash_vehicle' => $carro->id]))
            ->assertSee('111,00')
            ->assertDontSee('222,00');
    }

    public function test_historico_filtra_por_periodo(): void
    {
        $client = User::factory()->create(['role' => 'cliente']);
        $vehicle = Vehicle::factory()->create(['user_id' => $client->id]);

        Wash::factory()->create([
            'user_id' => $client->id, 'vehicle_id' => $vehicle->id,
            'service_name' => 'Lavagem simples', 'amount' => 111.00,
            'performed_at' => '2026-10-15 10:00:00',
        ]);
        Wash::factory()->create([
            'user_id' => $client->id, 'vehicle_id' => $vehicle->id,
            'service_name' => 'Lavagem simples', 'amount' => 222.00,
            'performed_at' => '2026-11-02 10:00:00',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.clients.show', [$client, 'wash_from' => '2026-10-01', 'wash_to' => '2026-10-31']))
            ->assertSee('111,00')
            ->assertDontSee('222,00');
    }

    /**
     * "Servicos mais utilizados" conta as lavagens (D-18), entao a lavagem
     * avulsa — atendimento que nunca teve agendamento (D-14) — tambem entra.
     */
    public function test_servicos_mais_utilizados_conta_a_lavagem_avulsa(): void
    {
        $client = User::factory()->create(['role' => 'cliente']);
        $vehicle = Vehicle::factory()->create(['user_id' => $client->id]);

        Wash::factory()->create([
            'user_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'appointment_id' => null,
            'service_name' => 'Polimento avulso',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.clients.show', $client))
            ->assertOk()
            ->assertSee('Servicos mais utilizados')
            ->assertSee('Polimento avulso');
    }

    public function test_historico_mostra_so_as_lavagens_do_proprio_cliente(): void
    {
        $client = User::factory()->create(['role' => 'cliente']);
        Vehicle::factory()->create(['user_id' => $client->id]);

        $outro = User::factory()->create(['role' => 'cliente']);
        $vehicleOutro = Vehicle::factory()->create(['user_id' => $outro->id]);
        Wash::factory()->create([
            'user_id' => $outro->id, 'vehicle_id' => $vehicleOutro->id,
            'service_name' => 'Lavagem de Outra Pessoa',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.clients.show', $client))
            ->assertDontSee('Lavagem de Outra Pessoa');
    }
}

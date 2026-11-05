<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Wash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * B-09 / RF06 / UC06-HU02 — lavagem avulsa: o cliente que chega sem ter
 * agendado tambem entra no faturamento (D-14).
 */
class StandaloneWashTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function client(): User
    {
        $client = User::factory()->create(['role' => 'cliente']);
        Vehicle::factory()->create(['user_id' => $client->id]);

        return $client;
    }

    public function test_admin_registra_lavagem_avulsa(): void
    {
        $client = $this->client();
        $service = Service::factory()->create(['name' => 'Lavagem Simples', 'price' => 45.00]);

        $this->actingAs($this->admin)
            ->post(route('admin.washes.store'), [
                'user_id' => $client->id,
                'vehicle_id' => $client->vehicles->first()->id,
                'service_id' => $service->id,
                'amount' => 45.00,
                'performed_at' => today()->format('Y-m-d'),
            ])
            ->assertRedirect(route('admin.washes.index'));

        $this->assertDatabaseHas('washes', [
            'user_id' => $client->id,
            // Avulsa: sem agendamento por tras.
            'appointment_id' => null,
            'service_name' => 'Lavagem Simples',
            'amount' => 45.00,
        ]);
    }

    public function test_nao_aceita_veiculo_de_outro_cliente(): void
    {
        $client = $this->client();
        $outro = $this->client();

        $this->actingAs($this->admin)
            ->post(route('admin.washes.store'), [
                'user_id' => $client->id,
                'vehicle_id' => $outro->vehicles->first()->id,
                'service_id' => Service::factory()->create()->id,
                'amount' => 45.00,
                'performed_at' => today()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('vehicle_id');

        $this->assertDatabaseCount('washes', 0);
    }

    public function test_campos_obrigatorios_da_avulsa(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.washes.store'), [
                'amount' => 45.00,
                'performed_at' => today()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors(['user_id', 'vehicle_id', 'service_id']);
    }

    public function test_listagem_filtra_por_periodo_e_soma_o_total(): void
    {
        $client = $this->client();
        $base = ['user_id' => $client->id, 'vehicle_id' => $client->vehicles->first()->id];

        Wash::factory()->create($base + ['amount' => 50.00, 'performed_at' => '2026-10-10 10:00:00']);
        Wash::factory()->create($base + ['amount' => 70.00, 'performed_at' => '2026-10-20 10:00:00']);
        Wash::factory()->create($base + ['amount' => 30.00, 'performed_at' => '2026-11-02 10:00:00']);

        $this->actingAs($this->admin)
            ->get(route('admin.washes.index', ['from' => '2026-10-01', 'to' => '2026-10-31']))
            ->assertOk()
            // Soma so as duas de outubro.
            ->assertSee('120,00')
            ->assertDontSee('30,00');
    }

    public function test_cliente_nao_acessa_as_telas_de_lavagem(): void
    {
        $client = $this->client();

        $this->actingAs($client)->get(route('admin.washes.index'))->assertForbidden();
        $this->actingAs($client)->get(route('admin.washes.create'))->assertForbidden();
    }
}

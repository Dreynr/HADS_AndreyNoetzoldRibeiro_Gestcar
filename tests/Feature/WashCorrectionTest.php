<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Wash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * B-09 / RF06 / UC06-HU03 — correcao de lavagem: so valor e observacoes
 * mudam. Cliente, veiculo, servico e data definem qual atendimento
 * aconteceu, e nao se corrigem.
 */
class WashCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function wash(): Wash
    {
        $client = User::factory()->create(['role' => 'cliente']);

        return Wash::factory()->create([
            'user_id' => $client->id,
            'vehicle_id' => Vehicle::factory()->create(['user_id' => $client->id])->id,
            'amount' => 50.00,
            'performed_at' => '2026-10-10 10:00:00',
        ]);
    }

    public function test_admin_corrige_valor_e_observacoes(): void
    {
        $wash = $this->wash();

        $this->actingAs($this->admin)
            ->patch(route('admin.washes.update', $wash), [
                'amount' => 42.50,
                'notes' => 'Valor digitado errado no registro.',
            ])
            ->assertRedirect(route('admin.washes.index'));

        $this->assertDatabaseHas('washes', [
            'id' => $wash->id,
            'amount' => 42.50,
            'notes' => 'Valor digitado errado no registro.',
        ]);
    }

    public function test_correcao_nao_muda_cliente_veiculo_servico_nem_data(): void
    {
        $wash = $this->wash();
        $original = $wash->only(['user_id', 'vehicle_id', 'service_id', 'service_name']);

        $outro = User::factory()->create(['role' => 'cliente']);
        $outroVeiculo = Vehicle::factory()->create(['user_id' => $outro->id]);
        $outroServico = Service::factory()->create(['name' => 'Outro Servico']);

        // Formulario adulterado: manda campos que nao deveria poder mudar.
        $this->actingAs($this->admin)
            ->patch(route('admin.washes.update', $wash), [
                'amount' => 50.00,
                'user_id' => $outro->id,
                'vehicle_id' => $outroVeiculo->id,
                'service_id' => $outroServico->id,
                'service_name' => 'Outro Servico',
                'performed_at' => '2026-11-04 10:00:00',
            ])
            ->assertRedirect();

        $wash->refresh();

        // Nenhum dos campos que identificam o atendimento foi alterado.
        $this->assertSame($original, $wash->only(['user_id', 'vehicle_id', 'service_id', 'service_name']));
        $this->assertSame('2026-10-10', $wash->performed_at->format('Y-m-d'));
    }

    public function test_valor_negativo_e_recusado_na_correcao(): void
    {
        $wash = $this->wash();

        $this->actingAs($this->admin)
            ->patch(route('admin.washes.update', $wash), ['amount' => -5])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseHas('washes', ['id' => $wash->id, 'amount' => 50.00]);
    }

    public function test_cliente_nao_corrige_lavagem(): void
    {
        $wash = $this->wash();

        $this->actingAs($wash->user)
            ->patch(route('admin.washes.update', $wash), ['amount' => 1.00])
            ->assertForbidden();

        $this->assertDatabaseHas('washes', ['id' => $wash->id, 'amount' => 50.00]);
    }

    public function test_tela_de_correcao_mostra_os_dados_fixos(): void
    {
        $wash = $this->wash();

        $this->actingAs($this->admin)
            ->get(route('admin.washes.edit', $wash))
            ->assertOk()
            ->assertSee($wash->user->name)
            ->assertSee($wash->service_name)
            ->assertSee('Apenas o valor e as observacoes podem ser corrigidos.');
    }
}

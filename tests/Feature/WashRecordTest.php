<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Wash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * B-09 / RF06 / D-13 / D-14 / D-17 — a lavagem e o registro do atendimento
 * realizado, guarda a propria copia do nome e do valor e nao pode ser
 * destruida pela exclusao de cliente, veiculo ou servico.
 */
class WashRecordTest extends TestCase
{
    use RefreshDatabase;

    private function wash(array $attributes = []): Wash
    {
        $user = User::factory()->create(['role' => 'cliente']);

        return Wash::factory()->create(array_merge([
            'user_id' => $user->id,
            'vehicle_id' => Vehicle::factory()->create(['user_id' => $user->id])->id,
        ], $attributes));
    }

    public function test_lavagem_guarda_nome_e_valor_do_momento_do_atendimento(): void
    {
        $service = Service::factory()->create([
            'name' => 'Lavagem Simples',
            'price' => 40.00,
        ]);

        $wash = Wash::fromService($service, [
            'user_id' => $user = User::factory()->create(['role' => 'cliente'])->id,
            'vehicle_id' => Vehicle::factory()->create(['user_id' => $user])->id,
            'performed_at' => now(),
        ]);
        $wash->save();

        // O preco do servico muda depois do atendimento.
        $service->update(['name' => 'Lavagem Simples (nova tabela)', 'price' => 70.00]);

        $wash->refresh();

        // D-13 e D-17: o fato nao muda quando a tabela de precos muda.
        $this->assertSame('40.00', $wash->amount);
        $this->assertSame('Lavagem Simples', $wash->service_name);
    }

    public function test_cliente_com_lavagem_e_inativado_em_vez_de_excluido(): void
    {
        $wash = $this->wash();
        $client = $wash->user;

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->delete(route('admin.clients.destroy', $client))
            ->assertRedirect(route('admin.clients.index'));

        $this->assertDatabaseHas('users', ['id' => $client->id, 'active' => false]);
        $this->assertDatabaseHas('washes', ['id' => $wash->id]);
    }

    public function test_veiculo_com_lavagem_nao_pode_ser_excluido(): void
    {
        $wash = $this->wash();

        $this->actingAs($wash->user)
            ->delete(route('vehicles.destroy', $wash->vehicle))
            ->assertRedirect(route('vehicles.index'));

        $this->assertDatabaseHas('vehicles', ['id' => $wash->vehicle_id]);
    }

    public function test_servico_com_lavagem_e_inativado_em_vez_de_excluido(): void
    {
        $wash = $this->wash();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->delete(route('admin.services.destroy', $wash->service))
            ->assertRedirect(route('admin.services.index'));

        $this->assertDatabaseHas('services', ['id' => $wash->service_id, 'active' => false]);
    }

    public function test_anonimizar_a_conta_mantem_a_lavagem_para_o_relatorio(): void
    {
        $wash = $this->wash(['amount' => 90.00]);

        $wash->user->anonymize();

        // D-07 com D-17: o dado pessoal sai, o dado do atendimento fica.
        $this->assertDatabaseHas('washes', [
            'id' => $wash->id,
            'amount' => 90.00,
            'service_name' => 'Lavagem simples',
        ]);
        $this->assertDatabaseHas('users', ['id' => $wash->user_id, 'name' => 'Cliente removido']);
    }

    public function test_fechamento_mensal_soma_so_as_lavagens_do_mes(): void
    {
        $this->wash(['amount' => 50.00, 'performed_at' => '2026-10-10 10:00:00']);
        $this->wash(['amount' => 70.00, 'performed_at' => '2026-10-31 20:00:00']);
        $this->wash(['amount' => 30.00, 'performed_at' => '2026-11-01 09:00:00']);

        $this->assertSame(120.0, (float) Wash::inMonth(2026, 10)->sum('amount'));
    }
}

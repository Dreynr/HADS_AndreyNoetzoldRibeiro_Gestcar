<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * B-10 / RF08 / UC08-HU01 e HU02 — o fechamento mensal sai das lavagens do
 * periodo (D-13), com faturamento, quantidade por servico, ticket medio e o
 * comparativo dos ultimos meses.
 */
class MonthlyClosingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /**
     * Criterio de aceite: o total tem que bater com a soma das lavagens do
     * periodo — nem a mais, nem a menos.
     */
    public function test_o_total_do_mes_e_a_soma_das_lavagens_do_mes(): void
    {
        Wash::factory()->create(['performed_at' => '2026-10-05 10:00:00', 'amount' => 80.00]);
        Wash::factory()->create(['performed_at' => '2026-10-20 15:00:00', 'amount' => 120.50]);

        // Vizinhos de mes, que nao podem entrar na conta.
        Wash::factory()->create(['performed_at' => '2026-09-30 23:00:00', 'amount' => 999.00]);
        Wash::factory()->create(['performed_at' => '2026-11-01 00:30:00', 'amount' => 999.00]);

        $fechamento = Wash::monthlyClosing(2026, 10);

        $this->assertEquals(200.50, $fechamento['total']);
        $this->assertSame(2, $fechamento['quantidade']);
    }

    public function test_o_ticket_medio_e_o_faturamento_dividido_pelas_lavagens(): void
    {
        Wash::factory()->create(['performed_at' => '2026-10-05 10:00:00', 'amount' => 100.00]);
        Wash::factory()->create(['performed_at' => '2026-10-06 10:00:00', 'amount' => 50.00]);

        $this->assertEquals(75.00, Wash::monthlyClosing(2026, 10)['ticket']);
    }

    public function test_mes_sem_lavagem_nao_divide_por_zero(): void
    {
        $fechamento = Wash::monthlyClosing(2026, 10);

        $this->assertEquals(0, $fechamento['total']);
        $this->assertSame(0, $fechamento['quantidade']);
        $this->assertEquals(0, $fechamento['ticket']);
        $this->assertTrue($fechamento['por_servico']->isEmpty());
    }

    /**
     * O agrupamento usa `service_name`, a copia gravada na lavagem (D-17), e
     * nao o nome atual do servico.
     */
    public function test_os_servicos_sao_agrupados_pelo_nome_gravado_na_lavagem(): void
    {
        Wash::factory()->create([
            'performed_at' => '2026-10-05 10:00:00',
            'service_name' => 'Lavagem simples',
            'amount' => 50.00,
        ]);
        Wash::factory()->create([
            'performed_at' => '2026-10-07 10:00:00',
            'service_name' => 'Lavagem simples',
            'amount' => 50.00,
        ]);
        Wash::factory()->create([
            'performed_at' => '2026-10-09 10:00:00',
            'service_name' => 'Polimento',
            'amount' => 300.00,
        ]);

        $porServico = Wash::monthlyClosing(2026, 10)['por_servico'];

        $this->assertCount(2, $porServico);

        // Vem ordenado pelo que mais faturou.
        $this->assertSame('Polimento', $porServico->first()->service_name);
        $this->assertEquals(1, $porServico->first()->quantidade);

        $simples = $porServico->firstWhere('service_name', 'Lavagem simples');
        $this->assertEquals(2, $simples->quantidade);
        $this->assertEquals(100.00, $simples->total);
    }

    public function test_o_comparativo_traz_os_meses_do_mais_antigo_para_o_mais_novo(): void
    {
        Wash::factory()->create(['performed_at' => '2026-08-10 10:00:00', 'amount' => 10.00]);
        Wash::factory()->create(['performed_at' => '2026-10-10 10:00:00', 'amount' => 70.00]);

        $comparativo = Wash::lastMonths(6, 2026, 10);

        $this->assertCount(6, $comparativo);
        $this->assertSame(5, $comparativo[0]['mes']);
        $this->assertSame(10, $comparativo[5]['mes']);
        $this->assertSame('10/2026', $comparativo[5]['rotulo']);

        $this->assertEquals(10.00, $comparativo[3]['total']);
        $this->assertEquals(70.00, $comparativo[5]['total']);
        $this->assertEquals(0, $comparativo[4]['total']);
    }

    public function test_o_comparativo_atravessa_a_virada_do_ano(): void
    {
        $comparativo = Wash::lastMonths(6, 2026, 2);

        $this->assertSame(2025, $comparativo[0]['ano']);
        $this->assertSame(9, $comparativo[0]['mes']);
        $this->assertSame(2026, $comparativo[5]['ano']);
    }

    public function test_a_tela_mostra_o_fechamento_do_mes_escolhido(): void
    {
        Wash::factory()->create([
            'performed_at' => '2026-10-05 10:00:00',
            'service_name' => 'Polimento',
            'amount' => 300.00,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.reports.index', ['mes' => 10, 'ano' => 2026]))
            ->assertOk()
            ->assertSee('Polimento')
            ->assertSee('300,00');
    }

    public function test_mes_invalido_e_recusado(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.reports.index', ['mes' => 13, 'ano' => 2026]))
            ->assertSessionHasErrors('mes');
    }

    public function test_cliente_nao_acessa_o_fechamento(): void
    {
        $cliente = User::factory()->create(['role' => 'cliente']);

        $this->actingAs($cliente)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }
}

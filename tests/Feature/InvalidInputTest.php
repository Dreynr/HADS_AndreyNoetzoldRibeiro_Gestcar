<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Entrada invalida pela URL nao pode derrubar tela nenhuma.
 *
 * Todo filtro deste sistema chega por query string, e query string e
 * digitavel: basta trocar na barra de endereco. O esperado e recusa ou lista
 * vazia — nunca erro 500.
 */
class InvalidInputTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_datas_invalidas_no_filtro_de_lavagens(): void
    {
        $resposta = $this->actingAs($this->admin())
            ->get(route('admin.washes.index', ['from' => 'abc', 'to' => 'xyz']));

        $this->assertNotSame(500, $resposta->status(), 'O filtro de lavagens quebrou com data invalida.');
    }

    public function test_mes_e_ano_invalidos_no_fechamento(): void
    {
        $resposta = $this->actingAs($this->admin())
            ->get(route('admin.reports.index', ['mes' => 'abc', 'ano' => 'xyz']));

        $this->assertNotSame(500, $resposta->status());
    }

    public function test_busca_estranha_no_estoque(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.stock.index', ['search' => "<script>alert('x')</script>"]))
            ->assertOk();
    }

    public function test_segmento_inexistente_na_listagem_de_clientes(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.clients.index', ['segment' => 'inventado']))
            ->assertOk();
    }

    public function test_data_invalida_nos_periodos_disponiveis(): void
    {
        $cliente = User::factory()->create(['role' => 'cliente']);

        $resposta = $this->actingAs($cliente)
            ->get(route('appointments.available-periods', ['date' => 'abc']));

        $this->assertNotSame(500, $resposta->status(), 'A consulta de periodos quebrou com data invalida.');
    }

    public function test_visao_invalida_na_agenda(): void
    {
        $resposta = $this->actingAs($this->admin())
            ->get(route('admin.appointments.index', ['view' => 'inventada', 'date' => 'abc']));

        $this->assertNotSame(500, $resposta->status(), 'A agenda quebrou com visao ou data invalida.');
    }

    public function test_datas_lotadas_com_mes_invalido(): void
    {
        $cliente = User::factory()->create(['role' => 'cliente']);

        $resposta = $this->actingAs($cliente)
            ->get(route('appointments.booked-dates', ['month' => 'abc', 'year' => 'xyz']));

        $this->assertNotSame(500, $resposta->status(), 'A consulta de datas lotadas quebrou.');
    }

    /**
     * Formulario enviado vazio tem que virar recusa de validacao (302 com
     * erros), nunca erro 500.
     */
    public function test_formularios_vazios_sao_recusados_sem_quebrar(): void
    {
        $admin = $this->admin();
        $cliente = User::factory()->create(['role' => 'cliente']);

        $posts = [
            'agendamento do cliente' => [$cliente, route('appointments.store')],
            'lavagem avulsa' => [$admin, route('admin.washes.store')],
            'produto do estoque' => [$admin, route('admin.stock.store')],
            'cliente novo' => [$admin, route('admin.clients.store')],
            'servico novo' => [$admin, route('admin.services.store')],
        ];

        foreach ($posts as $nome => [$usuario, $url]) {
            $resposta = $this->actingAs($usuario)->post($url, []);

            $this->assertNotSame(
                500,
                $resposta->status(),
                "O formulario de {$nome} quebrou quando enviado vazio."
            );
        }
    }
}

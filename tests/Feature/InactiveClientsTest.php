<?php

namespace Tests\Feature;

use App\Models\FollowUp;
use App\Models\User;
use App\Models\Wash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * B-11 / RF09 / UC09 — o painel lista quem passou de noventa dias sem
 * atendimento, de quem esta ha mais tempo sem vir para quem saiu por ultimo,
 * com contato manual pelo WhatsApp (D-05) e follow-up de retorno.
 */
class InactiveClientsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /**
     * Cria um cliente cuja ultima lavagem foi ha N dias.
     */
    private function clientLastSeen(string $name, ?int $diasAtras): User
    {
        $client = User::factory()->create([
            'role' => 'cliente',
            'name' => $name,
            'phone' => '5554999991111',
        ]);

        if ($diasAtras !== null) {
            Wash::factory()->create([
                'user_id' => $client->id,
                'performed_at' => now()->subDays($diasAtras),
            ]);
        }

        return $client;
    }

    public function test_so_aparece_quem_passou_de_noventa_dias(): void
    {
        $this->clientLastSeen('Sumido', 120);
        $this->clientLastSeen('Recente', 30);
        $this->clientLastSeen('No limite', 90);

        $this->actingAs($this->admin())
            ->get(route('admin.clients.inactive'))
            ->assertOk()
            ->assertSee('Sumido')
            ->assertDontSee('Recente')
            ->assertDontSee('No limite');
    }

    /**
     * Quem nunca foi atendido e novo, nao inativo — a mesma regra do
     * `segment()`, para o painel nao discordar do rotulo da listagem.
     */
    public function test_quem_nunca_veio_nao_entra_no_painel(): void
    {
        $this->clientLastSeen('Nunca veio', null);

        $this->actingAs($this->admin())
            ->get(route('admin.clients.inactive'))
            ->assertOk()
            ->assertDontSee('Nunca veio');
    }

    public function test_a_lista_vem_de_quem_esta_ha_mais_tempo_sem_vir(): void
    {
        $this->clientLastSeen('Faz tempo', 200);
        $this->clientLastSeen('Faz mais tempo ainda', 400);
        $this->clientLastSeen('Faz pouco mais de tres meses', 100);

        $response = $this->actingAs($this->admin())->get(route('admin.clients.inactive'));

        $conteudo = $response->getContent();
        $this->assertLessThan(
            strpos($conteudo, 'Faz tempo'),
            strpos($conteudo, 'Faz mais tempo ainda')
        );
        $this->assertLessThan(
            strpos($conteudo, 'Faz pouco mais de tres meses'),
            strpos($conteudo, 'Faz tempo')
        );
    }

    public function test_o_painel_mostra_o_link_do_whatsapp_do_cliente(): void
    {
        $this->clientLastSeen('Sumido', 120);

        $this->actingAs($this->admin())
            ->get(route('admin.clients.inactive'))
            ->assertSee('https://wa.me/5554999991111', false);
    }

    public function test_cliente_inativado_nao_aparece_no_painel(): void
    {
        $client = $this->clientLastSeen('Desligado', 120);
        $client->update(['active' => false]);

        $this->actingAs($this->admin())
            ->get(route('admin.clients.inactive'))
            ->assertDontSee('Desligado');
    }

    public function test_o_botao_cria_o_follow_up_de_retorno(): void
    {
        $client = $this->clientLastSeen('Sumido', 120);

        $this->actingAs($this->admin())
            ->from(route('admin.clients.inactive'))
            ->post(route('admin.clients.follow-ups.store', $client), [
                'type' => 'retorno',
                'title' => 'Chamar de volta',
                'due_date' => today()->format('Y-m-d'),
            ])
            ->assertRedirect(route('admin.clients.inactive'));

        $this->assertDatabaseHas('follow_ups', [
            'client_id' => $client->id,
            'type' => 'retorno',
            'title' => 'Chamar de volta',
            'completed' => false,
        ]);
    }

    public function test_cliente_nao_acessa_o_painel(): void
    {
        $cliente = User::factory()->create(['role' => 'cliente']);

        $this->actingAs($cliente)
            ->get(route('admin.clients.inactive'))
            ->assertForbidden();
    }

    /**
     * O filtro por segmento da listagem de clientes nao funcionava: ele
     * montava a colecao filtrada e jogava fora. UC09-HU01 pede que filtrar
     * por segmento funcione.
     */
    public function test_a_listagem_filtra_por_segmento(): void
    {
        $this->clientLastSeen('Sumido', 120);
        $this->clientLastSeen('Recente', 10);

        $this->actingAs($this->admin())
            ->get(route('admin.clients.index', ['segment' => 'inativo']))
            ->assertOk()
            ->assertSee('Sumido')
            ->assertDontSee('Recente');
    }

    public function test_sem_filtro_a_listagem_mostra_todos(): void
    {
        $this->clientLastSeen('Sumido', 120);
        $this->clientLastSeen('Recente', 10);

        $this->actingAs($this->admin())
            ->get(route('admin.clients.index'))
            ->assertOk()
            ->assertSee('Sumido')
            ->assertSee('Recente');
    }
}

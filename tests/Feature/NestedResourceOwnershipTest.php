<?php

namespace Tests\Feature;

use App\Models\ClientNote;
use App\Models\FollowUp;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Rotas aninhadas (`/clients/{client}/notes/{note}`) carregam os dois
 * registros separados: o Laravel nunca confere sozinho se um pertence ao
 * outro. Estes testes guardam essa conferencia.
 */
class NestedResourceOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function cliente(string $nome): User
    {
        return User::factory()->create(['role' => 'cliente', 'name' => $nome]);
    }

    public function test_nao_apaga_a_nota_de_outro_cliente(): void
    {
        $dono = $this->cliente('Dono da nota');
        $outro = $this->cliente('Outro cliente');

        $nota = ClientNote::create([
            'client_id' => $dono->id,
            'admin_id' => $this->admin()->id,
            'content' => 'Prefere atendimento de manha.',
        ]);

        // O id do outro cliente na URL, com a nota do dono.
        $this->actingAs($this->admin())
            ->delete(route('admin.clients.notes.destroy', [$outro, $nota]))
            ->assertNotFound();

        $this->assertDatabaseHas('client_notes', ['id' => $nota->id]);
    }

    public function test_apaga_a_nota_do_proprio_cliente(): void
    {
        $dono = $this->cliente('Dono da nota');

        $nota = ClientNote::create([
            'client_id' => $dono->id,
            'admin_id' => $this->admin()->id,
            'content' => 'Prefere atendimento de manha.',
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.clients.notes.destroy', [$dono, $nota]));

        $this->assertDatabaseMissing('client_notes', ['id' => $nota->id]);
    }

    public function test_nao_conclui_o_follow_up_de_outro_cliente(): void
    {
        $dono = $this->cliente('Dono do follow-up');
        $outro = $this->cliente('Outro cliente');

        $followUp = FollowUp::create([
            'client_id' => $dono->id,
            'type' => 'retorno',
            'title' => 'Chamar de volta',
            'due_date' => today(),
        ]);

        $this->actingAs($this->admin())
            ->patch(route('admin.clients.follow-ups.complete', [$outro, $followUp]))
            ->assertNotFound();

        $this->assertDatabaseHas('follow_ups', [
            'id' => $followUp->id,
            'completed' => false,
        ]);
    }

    /**
     * Configuracao salva em branco nao pode virar zero: com
     * `max_simple_per_period` igual a 0, ninguem conseguiria agendar.
     */
    public function test_configuracao_em_branco_cai_no_padrao(): void
    {
        Setting::setValue('max_simple_per_period', '');

        $this->assertSame('2', Setting::getValue('max_simple_per_period', '2'));
    }

    public function test_configuracao_preenchida_vence_o_padrao(): void
    {
        Setting::setValue('max_simple_per_period', '4');

        $this->assertSame('4', Setting::getValue('max_simple_per_period', '2'));
    }
}

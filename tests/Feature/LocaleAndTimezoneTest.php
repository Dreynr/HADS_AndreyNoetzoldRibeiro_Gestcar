<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fuso horario e idioma da interface.
 *
 * O fuso nao e detalhe de exibicao: o sistema decide "data futura",
 * "lavagem no futuro", o dia do lembrete e o mes do fechamento comparando com
 * `today()`. Em UTC, das 21h as 23h59 o Brasil ja estaria no dia seguinte
 * para o sistema, e todas essas regras sairiam do lugar.
 */
class LocaleAndTimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_o_sistema_roda_no_fuso_do_brasil(): void
    {
        $this->assertSame('America/Sao_Paulo', config('app.timezone'));
    }

    /**
     * A prova pratica: as 22h do horario de Brasilia, `today()` ainda tem que
     * ser o dia de hoje — em UTC ja seria o dia seguinte.
     */
    public function test_as_22h_o_sistema_ainda_esta_no_dia_de_hoje(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-11-15 22:30:00', 'America/Sao_Paulo'));

        $this->assertSame('2026-11-15', today()->format('Y-m-d'));
        $this->assertSame('2026-11-16', \Carbon\Carbon::tomorrow()->format('Y-m-d'));

        $this->travelBack();
    }

    public function test_a_tela_de_perfil_esta_em_portugues(): void
    {
        $cliente = User::factory()->create(['role' => 'cliente']);

        $this->actingAs($cliente)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Dados do perfil')
            ->assertSee('Nome')
            ->assertSee('Salvar')
            ->assertSee('Excluir conta')
            ->assertDontSee('Profile Information')
            ->assertDontSee('Delete Account');
    }
}

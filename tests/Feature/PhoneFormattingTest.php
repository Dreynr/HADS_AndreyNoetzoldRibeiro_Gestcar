<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * B-02 — o telefone e gravado no formato da Z-API (55 + DDD + numero) e
 * exibido no formato brasileiro, que e o que a validacao aceita de volta.
 */
class PhoneFormattingTest extends TestCase
{
    use RefreshDatabase;

    public function test_celular_de_nove_digitos_e_exibido_formatado(): void
    {
        $user = User::factory()->create(['phone' => '(54) 99999-1111']);

        $this->assertSame('5554999991111', $user->phone);
        $this->assertSame('(54) 99999-1111', $user->formattedPhone());
    }

    public function test_fixo_de_oito_digitos_e_exibido_formatado(): void
    {
        $user = User::factory()->create(['phone' => '(54) 3333-1111']);

        $this->assertSame('555433331111', $user->phone);
        $this->assertSame('(54) 3333-1111', $user->formattedPhone());
    }

    public function test_sem_telefone_devolve_nulo(): void
    {
        $user = User::factory()->create(['phone' => null]);

        $this->assertNull($user->formattedPhone());
    }

    public function test_o_perfil_carrega_o_telefone_em_formato_que_a_validacao_aceita(): void
    {
        $user = User::factory()->create(['phone' => '(54) 99999-1111']);

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSee('(54) 99999-1111', false)
            ->assertDontSee('5554999991111', false);
    }

    public function test_salvar_o_perfil_sem_mexer_no_telefone_funciona(): void
    {
        $user = User::factory()->create(['phone' => '(54) 99999-1111']);

        // Exatamente o que o navegador envia quando o cliente so troca o nome.
        $this->actingAs($user)->patch('/profile', [
            'name' => 'Nome Novo',
            'phone' => $user->formattedPhone(),
            'email' => $user->email,
        ])->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame('Nome Novo', $user->name);
        $this->assertSame('5554999991111', $user->phone);
    }
}

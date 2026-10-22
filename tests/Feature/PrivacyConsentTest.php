<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * B-06 / RNF09 / UC01-HU01 / UC10-HU04 / D-08 — politica publica, aceite
 * obrigatorio no cadastro e autorizacao do WhatsApp que pode ser mudada.
 */
class PrivacyConsentTest extends TestCase
{
    use RefreshDatabase;

    private function registrationPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Andrey Ribeiro',
            'phone' => '(54) 99999-0000',
            'email' => 'andrey@exemplo.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'privacy' => '1',
        ], $overrides);
    }

    public function test_a_politica_abre_sem_login(): void
    {
        $this->get('/politica-de-privacidade')
            ->assertOk()
            ->assertSee('Politica de Privacidade');
    }

    public function test_cadastro_sem_o_aceite_e_recusado(): void
    {
        $payload = $this->registrationPayload();
        unset($payload['privacy']);

        $this->post('/register', $payload)->assertSessionHasErrors('privacy');

        $this->assertGuest();
        $this->assertSame(0, User::where('email', 'andrey@exemplo.com')->count());
    }

    public function test_cadastro_com_o_aceite_grava_a_data(): void
    {
        $this->post('/register', $this->registrationPayload())->assertSessionHasNoErrors();

        $user = User::where('email', 'andrey@exemplo.com')->first();

        $this->assertNotNull($user->privacy_accepted_at);
    }

    public function test_a_caixa_do_whatsapp_vem_marcada_na_tela_de_cadastro(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('name="whatsapp_opt_in" value="1"', false)
            ->assertSee('checked', false);
    }

    public function test_desmarcando_o_whatsapp_no_cadastro_nao_autoriza(): void
    {
        // O formulario tem um input hidden com 0, entao o campo sempre vem.
        $this->post('/register', $this->registrationPayload(['whatsapp_opt_in' => '0']));

        $user = User::where('email', 'andrey@exemplo.com')->first();

        $this->assertFalse($user->whatsapp_opt_in);
        $this->assertNull($user->whatsapp_opt_in_at);
    }

    public function test_cadastro_sem_o_campo_do_whatsapp_nao_autoriza(): void
    {
        $this->post('/register', $this->registrationPayload());

        $user = User::where('email', 'andrey@exemplo.com')->first();

        $this->assertFalse($user->whatsapp_opt_in);
        $this->assertNull($user->whatsapp_opt_in_at);
    }

    public function test_cadastro_marcando_o_whatsapp_autoriza_e_grava_a_data(): void
    {
        $this->post('/register', $this->registrationPayload(['whatsapp_opt_in' => '1']));

        $user = User::where('email', 'andrey@exemplo.com')->first();

        $this->assertTrue($user->whatsapp_opt_in);
        $this->assertNotNull($user->whatsapp_opt_in_at);
    }

    public function test_cliente_pode_ligar_o_whatsapp_no_perfil(): void
    {
        $user = User::factory()->create([
            'phone' => '5554999990000',
            'whatsapp_opt_in' => false,
            'whatsapp_opt_in_at' => null,
        ]);

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'phone' => '(54) 99999-0000',
            'email' => $user->email,
            'whatsapp_opt_in' => '1',
        ])->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertTrue($user->whatsapp_opt_in);
        $this->assertNotNull($user->whatsapp_opt_in_at);
    }

    public function test_cliente_pode_desligar_o_whatsapp_no_perfil(): void
    {
        $user = User::factory()->create([
            'phone' => '5554999990000',
            'whatsapp_opt_in' => true,
            'whatsapp_opt_in_at' => now()->subDay(),
        ]);

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'phone' => '(54) 99999-0000',
            'email' => $user->email,
            'whatsapp_opt_in' => '0',
        ])->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertFalse($user->whatsapp_opt_in);
        $this->assertNull($user->whatsapp_opt_in_at);
    }

    public function test_salvar_o_perfil_sem_mudar_a_escolha_nao_mexe_na_data(): void
    {
        $antes = now()->subDays(3);

        $user = User::factory()->create([
            'phone' => '5554999990000',
            'whatsapp_opt_in' => true,
            'whatsapp_opt_in_at' => $antes,
        ]);

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Nome Novo',
            'phone' => '(54) 99999-0000',
            'email' => $user->email,
            'whatsapp_opt_in' => '1',
        ])->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame(
            $antes->format('Y-m-d H:i:s'),
            $user->whatsapp_opt_in_at->format('Y-m-d H:i:s')
        );
    }
}

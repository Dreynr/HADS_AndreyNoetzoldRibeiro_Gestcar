<?php

namespace Tests\Feature\Auth;

use App\Mail\PasswordResetCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * B-01 / RNF12 — a recuperacao de senha e por codigo de 6 digitos enviado
 * por e-mail, nao por link com token. O fluxo antigo do Breeze nao existe mais.
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function requestCodeFor(User $user): string
    {
        $this->post('/forgot-password', ['email' => $user->email]);

        return DB::table('password_reset_codes')
            ->where('email', $user->email)
            ->value('code');
    }

    public function test_a_tela_de_esqueci_a_senha_abre(): void
    {
        $this->get('/forgot-password')->assertOk();
    }

    public function test_pedir_o_codigo_envia_o_email_e_grava_o_codigo(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect(route('password.verify-code.form', ['email' => $user->email]));

        Mail::assertSent(PasswordResetCodeMail::class);

        $code = DB::table('password_reset_codes')->where('email', $user->email)->value('code');

        $this->assertSame(6, strlen($code));
    }

    public function test_email_nao_cadastrado_e_recusado(): void
    {
        Mail::fake();

        $this->post('/forgot-password', ['email' => 'ninguem@exemplo.com'])
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_codigo_correto_leva_a_tela_de_nova_senha(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $code = $this->requestCodeFor($user);

        $this->post('/verify-code', ['email' => $user->email, 'code' => $code])
            ->assertRedirect(route('password.reset.form'));

        $this->get('/reset-password')->assertOk();
    }

    public function test_codigo_errado_e_recusado(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $this->requestCodeFor($user);

        $this->post('/verify-code', ['email' => $user->email, 'code' => '000000'])
            ->assertSessionHasErrors('code');
    }

    public function test_codigo_expirado_e_recusado(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $code = $this->requestCodeFor($user);

        DB::table('password_reset_codes')
            ->where('email', $user->email)
            ->update(['expires_at' => now()->subMinute()]);

        $this->post('/verify-code', ['email' => $user->email, 'code' => $code])
            ->assertSessionHasErrors('code');
    }

    public function test_a_senha_e_trocada_e_o_codigo_e_apagado(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $code = $this->requestCodeFor($user);

        $this->post('/verify-code', ['email' => $user->email, 'code' => $code]);

        $this->post('/reset-password', [
            'password' => 'senha-nova-123',
            'password_confirmation' => 'senha-nova-123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('senha-nova-123', $user->fresh()->password));

        // O codigo nao pode servir de novo.
        $this->assertSame(
            0,
            DB::table('password_reset_codes')->where('email', $user->email)->count()
        );
    }

    public function test_nao_da_para_trocar_a_senha_sem_verificar_o_codigo(): void
    {
        $user = User::factory()->create();

        $this->post('/reset-password', [
            'password' => 'senha-nova-123',
            'password_confirmation' => 'senha-nova-123',
        ])->assertRedirect(route('password.request'));

        $this->assertFalse(Hash::check('senha-nova-123', $user->fresh()->password));
    }

    public function test_a_sexta_tentativa_de_codigo_no_mesmo_minuto_e_bloqueada(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $this->requestCodeFor($user);

        // RNF12: throttle de 5 por minuto na rota de verificacao.
        for ($tentativa = 1; $tentativa <= 5; $tentativa++) {
            $this->post('/verify-code', ['email' => $user->email, 'code' => '000000'])
                ->assertStatus(302);
        }

        $this->post('/verify-code', ['email' => $user->email, 'code' => '000000'])
            ->assertStatus(429);
    }
}

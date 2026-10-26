<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * B-07 itens 6 e 7 — separacao entre area do cliente e area do admin,
 * e o bloqueio de contas inativas no login (B-02, RF01).
 */
class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_cliente_nao_entra_na_agenda_do_admin(): void
    {
        $client = User::factory()->create(['role' => 'cliente']);

        $this->actingAs($client)
            ->get(route('admin.appointments.index'))
            ->assertForbidden();
    }

    public function test_admin_entra_na_agenda_do_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.appointments.index'))
            ->assertOk();
    }

    public function test_cliente_inativo_nao_consegue_entrar(): void
    {
        $user = User::factory()->create([
            'role' => 'cliente',
            'active' => false,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors();

        $this->assertGuest();
    }

    public function test_cliente_ativo_consegue_entrar(): void
    {
        $user = User::factory()->create([
            'role' => 'cliente',
            'active' => true,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }
}

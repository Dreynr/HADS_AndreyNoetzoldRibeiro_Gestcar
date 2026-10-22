<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\ClientNote;
use App\Models\FollowUp;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * B-05 / RF01 / UC01-HU05 / D-07 — quando o cliente exclui a propria conta,
 * os dados pessoais sao anonimizados e os agendamentos ficam.
 */
class AccountAnonymizationTest extends TestCase
{
    use RefreshDatabase;

    private function clientWithHistory(): User
    {
        $user = User::factory()->create([
            'name' => 'Andrey Ribeiro',
            'email' => 'andrey@exemplo.com',
            'phone' => '54999990000',
            'role' => 'cliente',
        ]);

        $vehicle = Vehicle::create([
            'user_id' => $user->id,
            'plate' => 'ABC1D23',
            'model' => 'Fusca',
            'color' => 'Azul',
        ]);

        $service = Service::create([
            'name' => 'Lavagem Externa Simples',
            'type' => 'principal',
            'price' => 40.00,
            'duration_minutes' => 45,
            'blocks_period' => false,
        ]);

        Appointment::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'scheduled_at' => now()->subMonth()->format('Y-m-d') . ' 17:00:00',
            'period' => 'noite',
            'status' => 'concluido',
        ]);

        ClientNote::create([
            'client_id' => $user->id,
            'admin_id' => User::factory()->create(['role' => 'admin'])->id,
            'content' => 'Prefere atendimento a noite.',
        ]);

        FollowUp::create([
            'client_id' => $user->id,
            'type' => 'retorno',
            'title' => 'Ligar para o cliente',
            'due_date' => today(),
            'completed' => false,
        ]);

        return $user;
    }

    public function test_dados_pessoais_sao_substituidos(): void
    {
        $user = $this->clientWithHistory();

        $this->actingAs($user)->delete('/profile', ['password' => 'password']);

        $user->refresh();

        $this->assertSame('Cliente removido', $user->name);
        $this->assertSame("removido-{$user->id}@gestcar.invalid", $user->email);
        $this->assertNull($user->phone);
        $this->assertNull($user->birthday);
        $this->assertFalse((bool) $user->active);
        $this->assertNotNull($user->anonymized_at);
    }

    public function test_os_agendamentos_continuam_na_agenda(): void
    {
        $user = $this->clientWithHistory();

        $this->actingAs($user)->delete('/profile', ['password' => 'password']);

        $this->assertSame(1, Appointment::where('user_id', $user->id)->count());
    }

    public function test_a_placa_some_mas_o_veiculo_fica(): void
    {
        $user = $this->clientWithHistory();

        $this->actingAs($user)->delete('/profile', ['password' => 'password']);

        $vehicle = Vehicle::where('user_id', $user->id)->first();

        $this->assertSame('REMOVIDA', $vehicle->plate);
        $this->assertSame('Fusca', $vehicle->model);
    }

    public function test_anotacoes_e_follow_ups_do_crm_sao_apagados(): void
    {
        $user = $this->clientWithHistory();

        $this->actingAs($user)->delete('/profile', ['password' => 'password']);

        $this->assertSame(0, ClientNote::where('client_id', $user->id)->count());
        $this->assertSame(0, FollowUp::where('client_id', $user->id)->count());
    }

    public function test_os_codigos_de_recuperacao_do_email_antigo_sao_apagados(): void
    {
        $user = $this->clientWithHistory();

        DB::table('password_reset_codes')->insert([
            'email' => 'andrey@exemplo.com',
            'code' => '123456',
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);

        $this->actingAs($user)->delete('/profile', ['password' => 'password']);

        $this->assertSame(
            0,
            DB::table('password_reset_codes')->where('email', 'andrey@exemplo.com')->count()
        );
    }

    public function test_o_email_original_fica_livre_para_novo_cadastro(): void
    {
        $user = $this->clientWithHistory();

        $this->actingAs($user)->delete('/profile', ['password' => 'password']);

        $novo = User::create([
            'name' => 'Andrey Ribeiro',
            'email' => 'andrey@exemplo.com',
            'phone' => '54999990000',
            'password' => 'password',
        ]);

        $this->assertNotSame($user->id, $novo->id);
    }

    public function test_cliente_anonimizado_sai_da_listagem_do_crm(): void
    {
        $user = $this->clientWithHistory();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->delete('/profile', ['password' => 'password']);

        $this->actingAs($admin)
            ->get(route('admin.clients.index'))
            ->assertOk()
            ->assertDontSee('Cliente removido');
    }

    public function test_admin_nao_pode_excluir_a_propria_conta(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->delete('/profile', ['password' => 'password'])
            ->assertSessionHasErrors('password', null, 'userDeletion');

        $admin->refresh();

        $this->assertNull($admin->anonymized_at);
        $this->assertTrue((bool) $admin->active);
    }
}

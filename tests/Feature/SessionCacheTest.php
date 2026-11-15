<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Depois de sair do sistema, o botao "voltar" do navegador nao pode mostrar
 * de novo a tela de quem estava logado.
 *
 * A sessao ja acabou e qualquer clique leva ao login, mas sem `no-store` o
 * navegador guarda a pagina e exibe o conteudo — placas, valores e dados de
 * cliente — na tela de um computador que pode ser de outra pessoa.
 */
class SessionCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_tela_de_quem_esta_logado_nao_pode_ficar_no_cache(): void
    {
        $cliente = User::factory()->create(['role' => 'cliente']);

        $cacheControl = $this->actingAs($cliente)
            ->get(route('appointments.index'))
            ->assertOk()
            ->headers->get('Cache-Control');

        // `no-cache` sozinho nao resolve: ele manda revalidar, e o botao
        // voltar nao revalida. `no-store` e o que proibe guardar.
        $this->assertStringContainsString('no-store', $cacheControl);
    }

    public function test_tela_do_admin_tambem_nao_fica_no_cache(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $cacheControl = $this->actingAs($admin)
            ->get(route('admin.appointments.index'))
            ->headers->get('Cache-Control');

        $this->assertStringContainsString('no-store', $cacheControl);
    }

    public function test_pagina_publica_pode_ficar_no_cache(): void
    {
        $cacheControl = $this->get(route('privacy'))
            ->assertOk()
            ->headers->get('Cache-Control');

        $this->assertStringNotContainsString('no-store', (string) $cacheControl);
    }
}

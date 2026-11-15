<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * B-12 / RF07 / UC07 — produtos com nome unico, entradas e saidas manuais,
 * saldo que nunca fica negativo e alerta de estoque minimo.
 */
class StockTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_cadastra_produto_e_o_saldo_inicial_vira_movimentacao(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.stock.store'), [
                'name' => 'Shampoo automotivo',
                'unit' => 'L',
                'quantity' => 10,
                'min_quantity' => 2,
            ])
            ->assertRedirect(route('admin.stock.index'));

        $product = Product::where('name', 'Shampoo automotivo')->first();

        $this->assertEquals(10, $product->quantity);

        // O primeiro saldo tambem tem historico: ele entrou como movimentacao.
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'entrada',
            'quantity' => 10,
        ]);
    }

    public function test_nao_cadastra_dois_produtos_com_o_mesmo_nome(): void
    {
        Product::factory()->create(['name' => 'Cera liquida']);

        $this->actingAs($this->admin())
            ->post(route('admin.stock.store'), [
                'name' => 'Cera liquida',
                'unit' => 'L',
                'min_quantity' => 1,
            ])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Product::where('name', 'Cera liquida')->count());
    }

    public function test_entrada_aumenta_o_saldo(): void
    {
        $product = Product::factory()->withBalance(5)->create(['unit' => 'L']);

        $this->actingAs($this->admin())
            ->post(route('admin.stock.movements.store', $product), [
                'type' => 'entrada',
                'quantity' => 3,
                'notes' => 'Compra do mes',
            ])
            ->assertSessionHas('success');

        $this->assertEquals(8, $product->fresh()->quantity);
    }

    public function test_saida_diminui_o_saldo(): void
    {
        $product = Product::factory()->withBalance(5)->create();

        $this->actingAs($this->admin())
            ->post(route('admin.stock.movements.store', $product), [
                'type' => 'saida',
                'quantity' => 2,
            ])
            ->assertSessionHas('success');

        $this->assertEquals(3, $product->fresh()->quantity);
    }

    /**
     * Regra do UC07-HU03: saida maior que o saldo e bloqueada com aviso, e
     * nao pode deixar nem a movimentacao nem o saldo alterados.
     */
    public function test_saida_maior_que_o_saldo_e_bloqueada(): void
    {
        $product = Product::factory()->withBalance(2)->create();

        $this->actingAs($this->admin())
            ->post(route('admin.stock.movements.store', $product), [
                'type' => 'saida',
                'quantity' => 5,
            ])
            ->assertSessionHas('error');

        $this->assertEquals(2, $product->fresh()->quantity);
        $this->assertSame(0, $product->movements()->count());
    }

    public function test_a_regra_do_saldo_negativo_vive_no_model(): void
    {
        $product = Product::factory()->withBalance(1)->create();

        $this->expectException(RuntimeException::class);

        $product->registerMovement(Product::TYPE_OUT, 2);
    }

    public function test_quantidade_zero_ou_negativa_e_recusada(): void
    {
        $product = Product::factory()->withBalance(5)->create();

        $this->actingAs($this->admin())
            ->post(route('admin.stock.movements.store', $product), [
                'type' => 'saida',
                'quantity' => 0,
            ])
            ->assertSessionHasErrors('quantity');
    }

    public function test_produto_com_movimentacao_e_inativado_em_vez_de_excluido(): void
    {
        $product = Product::factory()->create();
        $product->registerMovement(Product::TYPE_IN, 5);

        $this->actingAs($this->admin())
            ->delete(route('admin.stock.destroy', $product))
            ->assertRedirect(route('admin.stock.index'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'active' => false]);
    }

    public function test_produto_sem_movimentacao_e_excluido(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.stock.destroy', $product));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * O alerta pega quem ficou **menor ou igual** ao minimo (UC07-HU04).
     */
    public function test_o_alerta_mostra_quem_chegou_ao_minimo(): void
    {
        Product::factory()->withBalance(2)->create(['name' => 'Abaixo', 'min_quantity' => 5]);
        Product::factory()->withBalance(5)->create(['name' => 'No limite', 'min_quantity' => 5]);
        Product::factory()->withBalance(9)->create(['name' => 'Sobrando', 'min_quantity' => 5]);

        $alertas = Product::active()->belowMinimum()->pluck('name');

        $this->assertContains('Abaixo', $alertas);
        $this->assertContains('No limite', $alertas);
        $this->assertNotContains('Sobrando', $alertas);
    }

    public function test_produto_inativo_nao_entra_no_alerta(): void
    {
        Product::factory()->withBalance(0)->create([
            'name' => 'Descontinuado',
            'min_quantity' => 5,
            'active' => false,
        ]);

        $this->assertNotContains(
            'Descontinuado',
            Product::active()->belowMinimum()->pluck('name')
        );
    }

    public function test_a_edicao_nao_mexe_no_saldo(): void
    {
        $product = Product::factory()->withBalance(7)->create(['name' => 'Cera', 'unit' => 'L']);

        $this->actingAs($this->admin())
            ->patch(route('admin.stock.update', $product), [
                'name' => 'Cera carnauba',
                'unit' => 'L',
                'min_quantity' => 3,
                'quantity' => 999,
            ]);

        $product->refresh();

        $this->assertSame('Cera carnauba', $product->name);
        $this->assertEquals(7, $product->quantity);
    }

    /**
     * As quatro telas do estoque abrem. Sem isto, um erro de Blade so
     * apareceria no navegador.
     */
    public function test_as_telas_do_estoque_abrem(): void
    {
        $product = Product::factory()->withBalance(1)->create(['name' => 'Cera', 'min_quantity' => 5]);
        $product->registerMovement(Product::TYPE_IN, 2, 'Compra');

        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.stock.index'))
            ->assertOk()
            ->assertSee('Cera')
            ->assertSee('estoque minimo');

        $this->actingAs($admin)->get(route('admin.stock.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.stock.edit', $product))->assertOk();

        $this->actingAs($admin)->get(route('admin.stock.movements', $product))
            ->assertOk()
            ->assertSee('Compra');
    }

    public function test_cliente_nao_acessa_o_estoque(): void
    {
        $cliente = User::factory()->create(['role' => 'cliente']);

        $this->actingAs($cliente)
            ->get(route('admin.stock.index'))
            ->assertForbidden();
    }
}

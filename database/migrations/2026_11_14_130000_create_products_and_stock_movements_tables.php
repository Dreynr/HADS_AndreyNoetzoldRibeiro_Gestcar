<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estoque de produtos (B-12 / RF07 / UC07).
 *
 * Duas tabelas: o produto guarda o saldo atual, e cada entrada ou saida vira
 * uma linha em `stock_movements`. O saldo podia ser so a soma das
 * movimentacoes, mas guardar o numero pronto deixa a listagem e o alerta de
 * minimo em uma consulta — e o historico continua na outra tabela, para
 * conferir de onde o saldo veio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Nome unico: a regra de negocio do UC07-HU01.
            $table->string('name', 80)->unique();

            // Unidade e texto livre curto (un, L, ml, kg) porque cada produto
            // da estetica vem numa medida diferente.
            $table->string('unit', 10);

            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('min_quantity', 10, 2);
            $table->boolean('active')->default(true);

            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // Sem cascade: produto com movimentacao nao e excluido, e
            // inativado (UC07-HU02), entao o historico nunca fica orfao.
            $table->foreignId('product_id')->constrained();

            $table->string('type', 10);
            $table->decimal('quantity', 10, 2);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('products');
    }
};

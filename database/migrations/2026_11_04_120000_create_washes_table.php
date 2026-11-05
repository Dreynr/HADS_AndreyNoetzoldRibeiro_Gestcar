<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A lavagem e o fato: o atendimento que realmente aconteceu (D-14).
     * O agendamento e so a intencao, e nem toda lavagem foi agendada.
     *
     * A tabela guarda a propria copia do que o fechamento mensal precisa
     * (`service_name` e `amount`), para o relatorio de um mes fechado nao
     * mudar quando o preco ou o nome do servico mudarem (D-13 e D-17) e
     * para o faturamento nao depender do cadastro do cliente, que pode ser
     * anonimizado a pedido do titular (D-07).
     */
    public function up(): void
    {
        Schema::create('washes', function (Blueprint $table) {
            $table->id();

            // Nulo = lavagem avulsa, sem agendamento. `nullOnDelete` mantem a
            // lavagem mesmo se o agendamento for apagado: o fato nao depende
            // da intencao que o originou.
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();

            // Sem `cascadeOnDelete` de proposito: a chave estrangeira barra
            // apagar cliente, veiculo ou servico que tenha lavagem. Quem tem
            // historico e inativado, nunca excluido.
            $table->foreignId('user_id')->constrained();
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('service_id')->constrained();

            // Copia do nome no momento do atendimento (D-17).
            $table->string('service_name', 80);

            // Valor cobrado no momento do atendimento, editavel pelo admin
            // para desconto ou ajuste (D-13).
            $table->decimal('amount', 10, 2);

            $table->text('notes')->nullable();
            $table->dateTime('performed_at');
            $table->timestamps();

            // O fechamento mensal filtra por periodo (B-10).
            $table->index('performed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('washes');
    }
};

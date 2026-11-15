<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de cada tentativa de aviso ao cliente (B-08).
 *
 * Serve a tres coisas de uma vez: garante que o mesmo aviso nao saia duas
 * vezes pelo mesmo canal, guarda o motivo das falhas para a gente saber o que
 * aconteceu, e da os numeros de envio para a conclusao do TCC.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();

            // Nulo porque nem todo aviso nasce de um agendamento.
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('channel', 20);
            $table->string('type', 40);
            $table->string('status', 10);
            $table->text('error')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // E este indice que impede o envio repetido: um aviso de um tipo,
            // por um canal, para um agendamento, so entra uma vez.
            $table->unique(['appointment_id', 'channel', 'type'], 'notification_logs_unico');

            // Para a consulta do painel, que olha por cliente e por data.
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};

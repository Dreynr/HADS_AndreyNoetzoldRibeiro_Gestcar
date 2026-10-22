<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Colunas da LGPD (D-07 e D-08):
     * - privacy_accepted_at / whatsapp_opt_in / whatsapp_opt_in_at: consentimento (B-06)
     * - anonymized_at: marca a conta anonimizada a pedido do titular (B-05)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('privacy_accepted_at')->nullable()->after('preferences');
            $table->boolean('whatsapp_opt_in')->default(false)->after('privacy_accepted_at');
            $table->timestamp('whatsapp_opt_in_at')->nullable()->after('whatsapp_opt_in');
            $table->timestamp('anonymized_at')->nullable()->after('whatsapp_opt_in_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'privacy_accepted_at',
                'whatsapp_opt_in',
                'whatsapp_opt_in_at',
                'anonymized_at',
            ]);
        });
    }
};

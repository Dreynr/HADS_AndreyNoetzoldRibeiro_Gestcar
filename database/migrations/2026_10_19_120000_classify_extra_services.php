<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Serviços vendidos como adicional de uma lavagem, e não sozinhos.
     * A coluna `type` nasceu com o padrão 'principal', então os serviços
     * que já existiam precisam ser reclassificados.
     */
    private const EXTRAS = [
        'Cristalizacao Vidro Dianteiro' => false,
        'Descontaminacao de Chuva Acida' => true,
    ];

    public function up(): void
    {
        foreach (self::EXTRAS as $name => $blocksPeriod) {
            DB::table('services')
                ->where('name', $name)
                ->update([
                    'type' => 'extra',
                    'blocks_period' => $blocksPeriod,
                ]);
        }
    }

    public function down(): void
    {
        foreach (array_keys(self::EXTRAS) as $name) {
            DB::table('services')
                ->where('name', $name)
                ->update([
                    'type' => 'principal',
                    'blocks_period' => false,
                ]);
        }
    }
};

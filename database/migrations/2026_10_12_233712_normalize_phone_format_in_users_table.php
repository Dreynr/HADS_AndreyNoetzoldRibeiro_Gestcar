<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereNotNull('phone')->orderBy('id')->each(function ($user) {
            $digits = preg_replace('/\D/', '', $user->phone);

            if (!str_starts_with($digits, '55')) {
                $digits = '55' . $digits;
            }

            DB::table('users')->where('id', $user->id)->update(['phone' => $digits]);
        });
    }

    public function down(): void
    {
        // Não é possível reverter a normalização — o formato original já foi perdido.
    }
};

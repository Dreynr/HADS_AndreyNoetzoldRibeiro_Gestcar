<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'phone' => '5554999990000',
            'email' => 'admin@gestcar.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Cliente Teste',
            'phone' => '5554999991111',
            'email' => 'cliente@teste.com',
            'password' => Hash::make('password'),
            'role' => 'cliente',
        ]);

        Service::create([
            'name' => 'Lavagem Externa Simples',
            'type' => 'principal',
            'description' => 'Lavagem externa com shampoo automotivo e secagem.',
            'price' => 40.00,
            'duration_minutes' => 45,
            'blocks_period' => false,
        ]);

        Service::create([
            'name' => 'Lavagem Externa e Interna Simples',
            'type' => 'principal',
            'description' => 'Lavagem externa e interna com aspiracao e limpeza geral.',
            'price' => 60.00,
            'duration_minutes' => 60,
            'blocks_period' => false,
        ]);

        Service::create([
            'name' => 'Lavagem Completa com Cera',
            'type' => 'principal',
            'description' => 'Lavagem completa com cera e restauracao de plasticos internos e externos.',
            'price' => 120.00,
            'duration_minutes' => 180,
            'blocks_period' => true,
        ]);

        Service::create([
            'name' => 'Cristalizacao Vidro Dianteiro',
            'type' => 'extra',
            'description' => 'Cristalizacao do vidro dianteiro para maior visibilidade e protecao.',
            'price' => 50.00,
            'duration_minutes' => 45,
            'blocks_period' => false,
        ]);

        Service::create([
            'name' => 'Descontaminacao de Chuva Acida',
            'type' => 'extra',
            'description' => 'Remocao de manchas de chuva acida dos vidros. Valor varia de R$50 a R$80 conforme quantidade de vidros.',
            'price' => 50.00,
            'duration_minutes' => 60,
            'blocks_period' => false,
        ]);

        Setting::setValue('weekday_start', '17');
        Setting::setValue('weekend_start', '8');
        Setting::setValue('close_hour', '22');
        Setting::setValue('work_weekends', '1');
        Setting::setValue('max_simple_per_period', '2');
    }
}

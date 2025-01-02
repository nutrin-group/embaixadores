<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Promocode;
use Illuminate\Database\Seeder;

class PromocodeSeeder extends Seeder
{
    public function run(): void
    {
        // Busca os usuários necessários
        $manager = User::where('email', 'antonioolucass@gmail.com')->first();
        $pix7off = User::where('email', 'teste@nutrin.com.br')->first();
        $saudade = User::where('email', 'embaixador2@nutrin.com.br')->first();

        // Cria Promocode para PIX7OFF
        Promocode::create([
            'name' => 'PIX7OFF',
            'manager_id' => $manager->id,
            'user_id' => $pix7off->id
        ]);

        // Cria Promocode para SAUDADE
        Promocode::create([
            'name' => 'SAUDADE',
            'manager_id' => $manager->id,
            'user_id' => $saudade->id
        ]);
    }
}

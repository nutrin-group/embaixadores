<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Conta Admin
        User::create([
            
            'name' => 'Antonio Admin',
            'email' => 'antonio.duarte@nutrin.com.br',
            'password' => Hash::make('Rpk_101298'),
            'type' => 'admin',
            'status' => 'active'
        ]);

        // Conta Manager
        User::create([
            'name' => 'Antonio Manager',
            'email' => 'antonioolucass@gmail.com',
            'password' => Hash::make('Rpk_101298'),
            'type' => 'manager',
            'status' => 'active'
        ]);

        // Conta Embaixador 1 (pix7off)
        User::create([
            'name' => 'Embaixador PIX7OFF',
            'email' => 'teste@nutrin.com.br',
            'password' => Hash::make('Rpk_101298'),
            'type' => 'ambassador',
            'status' => 'active'
        ]);

        // Conta Embaixador 2 (SAUDADE)
        User::create([
            'name' => 'Embaixador SAUDADE',
            'email' => 'embaixador2@nutrin.com.br',
            'password' => Hash::make('1234'),
            'type' => 'ambassador',
            'status' => 'active'
        ]);
    }
}

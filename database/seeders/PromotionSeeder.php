<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        Promotion::create([
            'commission_type' => 'percentage',
            'amount' => 10.00,
            'start_date' => now(),
            'end_date' => now()->addDays(210),
            'is_active' => true
        ]);
    }
}

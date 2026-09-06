<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            ['code' => 'RMB', 'name' => 'Chinese Yuan', 'rate' => 1.000000, 'is_base' => true, 'is_active' => true],
            ['code' => 'USD', 'name' => 'US Dollar', 'rate' => 0.137000, 'is_base' => false, 'is_active' => true],
            ['code' => 'EUR', 'name' => 'Euro', 'rate' => 0.126000, 'is_base' => false, 'is_active' => true],
            ['code' => 'GBP', 'name' => 'British Pound', 'rate' => 0.108000, 'is_base' => false, 'is_active' => true],
        ];

        foreach ($rates as $rate) {
            ExchangeRate::updateOrCreate(
                ['code' => $rate['code']],
                array_merge($rate, ['last_updated_at' => now()])
            );
        }
    }
}

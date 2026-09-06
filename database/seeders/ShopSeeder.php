<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('shops')->updateOrInsert(
            ['id' => 1],
            [
                'vendor_id'   => 2,
                'name'        => 'ZY Energy',
                'slug'        => 'zy-energy',
                'description' => 'ZY Energy — Wind Turbines, Solar Panels & Energy Storage',
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );
    }
}

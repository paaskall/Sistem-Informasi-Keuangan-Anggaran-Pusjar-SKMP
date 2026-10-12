<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            MasterDataSeeder::class,
            TargetSeeder::class,
            RealisasiSeeder::class,
            PengadaanSeeder::class,
            KendalaSeeder::class,
            TargetSeederHistorical::class,
            RealisasiSeederHistorical::class,
            PnbpSeeder::class,  
        ]);
    }
}
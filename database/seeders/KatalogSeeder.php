<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class KatalogSeeder extends Seeder
{
    /**
     * Seed the application's catalog data.
     */
    public function run(): void
    {
        $this->call([
            JenjangSeeder::class,
            ProgramSeeder::class,
            ProgramJenjangSeeder::class,
            PaketSeeder::class,
            PaketHargaSeeder::class,
        ]);
    }
}

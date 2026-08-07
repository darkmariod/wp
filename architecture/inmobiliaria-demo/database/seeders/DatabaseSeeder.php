<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CitySeeder::class,
            SectorSeeder::class,
            PropertyTypeSeeder::class,
            OperationSeeder::class,
            PropertySeeder::class,
        ]);
    }
}

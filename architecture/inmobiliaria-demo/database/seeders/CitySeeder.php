<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = ['Riobamba', 'Guano', 'Penipe', 'Chambo'];

        foreach ($cities as $name) {
            City::create(compact('name'));
        }

        $this->command->info('✓ ' . count($cities) . ' ciudades creadas');
    }
}

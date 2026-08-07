<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        $sectors = [
            ['name' => 'Centro',        'city_id' => 1, 'visibility' => 'visible'],
            ['name' => 'Norte',         'city_id' => 1, 'visibility' => 'visible'],
            ['name' => 'Sur',           'city_id' => 1, 'visibility' => 'visible'],
            ['name' => 'Lizarzaburu',   'city_id' => 1, 'visibility' => 'visible'],
            ['name' => 'Maldonado',     'city_id' => 1, 'visibility' => 'visible'],
            ['name' => 'Yaruquíes',     'city_id' => 1, 'visibility' => 'visible'],
            ['name' => 'La Primavera',  'city_id' => 1, 'visibility' => 'visible'],
            ['name' => 'San Alfonso',   'city_id' => 1, 'visibility' => 'visible'],
        ];

        foreach ($sectors as $data) {
            Sector::create($data);
        }

        $this->command->info('✓ ' . count($sectors) . ' sectores creados');
    }
}

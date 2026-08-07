<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;

class PropertyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Casa', 'Departamento', 'Terreno', 'Local Comercial'];

        foreach ($types as $name) {
            PropertyType::create(compact('name'));
        }
    }
}

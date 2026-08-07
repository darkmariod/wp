<?php

namespace Database\Seeders;

use App\Models\Operation;
use Illuminate\Database\Seeder;

class OperationSeeder extends Seeder
{
    public function run(): void
    {
        Operation::create(['name' => 'Venta']);
        Operation::create(['name' => 'Alquiler']);
    }
}

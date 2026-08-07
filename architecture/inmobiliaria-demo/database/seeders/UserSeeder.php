<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Administrador',
            'email'    => 'administrador@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->command->info('✓ Usuario administrador creado');
    }
}

<?php

namespace Database\Factories;

use App\Models\AnticipoCliente;
use App\Models\Obra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnticipoCliente>
 */
class AnticipoClienteFactory extends Factory
{
    protected $model = AnticipoCliente::class;

    public function definition(): array
    {
        return [
            'obra_id' => Obra::factory(),
            'monto_total' => fake()->randomFloat(2, 5000, 100000),
            'porcentaje' => fake()->randomFloat(2, 5, 30),
            'estado' => 'pendiente',
            'fecha_concesion' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\PlanCuenta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanCuenta>
 */
class PlanCuentaFactory extends Factory
{
    protected $model = PlanCuenta::class;

    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->bothify('##.##.##'),
            'nombre' => fake()->words(2, true),
            'grupo' => fake()->randomElement(['activo', 'pasivo', 'patrimonio', 'ingreso', 'gasto']),
            'tipo' => fake()->randomElement(['deudor', 'acreedor']),
            'es_auxiliar' => false,
            'padre_id' => null,
            'activa' => true,
        ];
    }
}

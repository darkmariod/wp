<?php

namespace Database\Factories;

use App\Models\AsientoContable;
use App\Models\DetalleAsiento;
use App\Models\PlanCuenta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleAsiento>
 */
class DetalleAsientoFactory extends Factory
{
    protected $model = DetalleAsiento::class;

    public function definition(): array
    {
        return [
            'asiento_id' => AsientoContable::factory(),
            'cuenta_id' => PlanCuenta::factory(),
            'debe' => 0,
            'haber' => 0,
            'referencia' => fake()->optional()->sentence(3),
        ];
    }
}

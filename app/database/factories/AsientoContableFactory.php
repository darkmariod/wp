<?php

namespace Database\Factories;

use App\Models\AsientoContable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AsientoContable>
 */
class AsientoContableFactory extends Factory
{
    protected $model = AsientoContable::class;

    public function definition(): array
    {
        return [
            'numero_asiento' => fake()->unique()->bothify('ASI-######'),
            'fecha' => fake()->dateTimeThisMonth(),
            'descripcion' => fake()->sentence(5),
            'obra_id' => null,
            'tipo' => 'manual',
            'estado' => 'borrador',
            'usuario_creacion' => User::factory(),
            'usuario_aprobacion' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\DetalleAPU;
use App\Models\Presupuesto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleAPU>
 */
class DetalleAPUFactory extends Factory
{
    protected $model = DetalleAPU::class;

    public function definition(): array
    {
        $cantidad = fake()->randomFloat(4, 1, 100);
        $costoUnitario = fake()->randomFloat(6, 1, 200);

        return [
            'presupuesto_id' => Presupuesto::factory(),
            'tipo' => fake()->randomElement(['material', 'mano_obra', 'subcontrato', 'equipo']),
            'descripcion' => fake()->words(3, true),
            'unidad_medida' => fake()->randomElement(['m', 'm2', 'kg', 'un', 'lt']),
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'costo_total' => round($cantidad * $costoUnitario, 2),
        ];
    }
}

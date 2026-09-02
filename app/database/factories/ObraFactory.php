<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Obra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Obra>
 */
class ObraFactory extends Factory
{
    protected $model = Obra::class;

    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->bothify('OBR-####'),
            'nombre' => fake()->sentence(3),
            'cliente_id' => Cliente::factory(),
            'direccion' => fake()->address(),
            'fecha_inicio' => fake()->dateTimeBetween('-6 months', '-1 month'),
            'fecha_fin_estimada' => fake()->dateTimeBetween('+1 month', '+6 months'),
            'fecha_fin_real' => null,
            'estado' => 'en_curse',
            'contrato_monto' => fake()->randomFloat(2, 10000, 500000),
            'anticipo_porcentaje' => 10.00,
            'aiu_administracion' => 10.00,
            'aiu_imprevistos' => 5.00,
            'aiu_utilidad' => 10.00,
            'costo_fijo_mensual' => fake()->randomFloat(2, 1000, 10000),
        ];
    }
}

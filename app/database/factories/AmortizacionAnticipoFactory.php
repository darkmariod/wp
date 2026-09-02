<?php

namespace Database\Factories;

use App\Models\AmortizacionAnticipo;
use App\Models\AnticipoCliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AmortizacionAnticipo>
 */
class AmortizacionAnticipoFactory extends Factory
{
    protected $model = AmortizacionAnticipo::class;

    public function definition(): array
    {
        return [
            'anticipo_id' => AnticipoCliente::factory(),
            'numero_amortizacion' => fake()->numberBetween(1, 10),
            'porcentaje_amortizar' => fake()->randomFloat(2, 10, 50),
            'monto_amortizado' => fake()->randomFloat(2, 500, 10000),
            'avance_porcentaje' => fake()->randomFloat(2, 10, 100),
            'fecha_amortizacion' => fake()->dateTimeBetween('-2 months', 'now'),
            'asiento_id' => null,
        ];
    }
}

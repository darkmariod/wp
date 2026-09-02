<?php

namespace Database\Factories;

use App\Models\AsistenciaObra;
use App\Models\Obra;
use App\Models\Trabajador;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AsistenciaObra>
 */
class AsistenciaObraFactory extends Factory
{
    protected $model = AsistenciaObra::class;

    public function definition(): array
    {
        return [
            'obra_id' => Obra::factory(),
            'trabajador_id' => Trabajador::factory(),
            'fecha' => fake()->dateTimeBetween('-1 month', 'now'),
            'horas_trabajadas' => fake()->randomFloat(2, 4, 10),
            'hora_entrada' => fake()->time('H:i'),
            'hora_salida' => fake()->time('H:i'),
            'tipo_jornada' => fake()->randomElement(['normal', 'extraordinaria', 'dominical_feriado']),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}

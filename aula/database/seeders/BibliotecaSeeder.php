<?php

namespace Database\Seeders;

use App\Models\ResourceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BibliotecaSeeder extends Seeder
{
    /**
     * Categorías base de la Biblioteca. Es estructura, no data de prueba:
     * se puede correr en producción con
     * `php artisan db:seed --class=BibliotecaSeeder` y DatabaseSeeder no
     * la llama a propósito.
     *
     * Es idempotente: cada categoría se busca por su slug, así que correrlo
     * otra vez no duplica nada ni pisa lo que el colegio haya editado.
     */
    public function run(): void
    {
        $categorias = [
            ['Libros', 'Libros y textos de consulta.', true],
            ['Material de clase', 'Recursos que el docente usa o entrega en sus clases.', true],
            ['Material académico', 'Documentos de apoyo académico y normativo.', false],
            ['Material complementario', 'Lecturas, enlaces e imágenes para ampliar lo visto en clase.', false],
        ];

        foreach ($categorias as $i => [$nombre, $descripcion, $enPestanas]) {
            ResourceCategory::firstOrCreate(
                ['slug' => Str::slug($nombre)],
                [
                    'name' => $nombre,
                    'description' => $descripcion,
                    'sort_order' => $i + 1,
                    'show_in_tabs' => $enPestanas,
                    'is_active' => true,
                ],
            );
        }

        $this->command?->info('Biblioteca: categorías base listas.');
    }
}

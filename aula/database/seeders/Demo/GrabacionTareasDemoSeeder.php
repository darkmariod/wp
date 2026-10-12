<?php

namespace Database\Seeders\Demo;

use App\Models\Area;
use App\Models\Child;
use App\Models\Content;
use App\Models\Environment;
use App\Models\Family;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Escenario para grabar el video del flujo de tareas, SOLO para local: dos
 * docentes con su ambiente, dos familias con sus hijos y tres tareas
 * publicadas por ambiente que piden entrega. No lo llama DatabaseSeeder y se
 * niega a correr en producción.
 *
 * Es idempotente y no toca nada que no sea suyo: todo se busca por correo,
 * nombre o slug, y si un nombre de la demo ya lo usa otra persona se aborta
 * sin escribir. En cada corrida reubica las fechas límite respecto a hoy y
 * repone el estado de las seis tareas, pero nunca crea entregas.
 */
class GrabacionTareasDemoSeeder extends Seeder
{
    public const PASSWORD = 'password';

    public const DOCENTE_GIRASOLES = 'docente.girasoles@demo.pestalozzi.test';

    public const DOCENTE_ESTRELLAS = 'docente.estrellas@demo.pestalozzi.test';

    public const FAMILIA_DEMO = 'familia.demo@demo.pestalozzi.test';

    public const FAMILIA_PRUEBA = 'familia.prueba@demo.pestalozzi.test';

    /**
     * Correo => nombre, rol y familia (solo las cuentas de familia).
     *
     * @var array<string, array{name: string, role: string, family: ?string}>
     */
    private const CUENTAS = [
        self::DOCENTE_GIRASOLES => ['name' => 'Mónica Paredes', 'role' => User::ROLE_GUIA, 'family' => null],
        self::DOCENTE_ESTRELLAS => ['name' => 'Luis Andrade', 'role' => User::ROLE_GUIA, 'family' => null],
        self::FAMILIA_DEMO => ['name' => 'Carlos Mena', 'role' => User::ROLE_FAMILIA, 'family' => 'Familia Mena'],
        self::FAMILIA_PRUEBA => ['name' => 'Daniela Cruz', 'role' => User::ROLE_FAMILIA, 'family' => 'Familia Cruz'],
    ];

    /**
     * Nombre => docente titular, etapa y edades. Estrellas es "razonadoras":
     * ahí solo se permiten lecturas y tareas, y la tarea es lo que se graba.
     *
     * @var array<string, array{teacher: string, stage: string, age_range: string, order: int}>
     */
    private const AMBIENTES = [
        'Girasoles' => ['teacher' => self::DOCENTE_GIRASOLES, 'stage' => Environment::STAGE_ABSORBENTES, 'age_range' => '3 a 6 años', 'order' => 90],
        'Estrellas' => ['teacher' => self::DOCENTE_ESTRELLAS, 'stage' => Environment::STAGE_RAZONADORAS, 'age_range' => '6 a 9 años', 'order' => 91],
    ];

    /**
     * Niño => familia, ambiente y edad (años, meses) al crearse.
     *
     * @var array<string, array{family: string, environment: string, years: int, months: int}>
     */
    private const NINOS = [
        'Sofía Mena' => ['family' => 'Familia Mena', 'environment' => 'Girasoles', 'years' => 5, 'months' => 3],
        'Mateo Mena' => ['family' => 'Familia Mena', 'environment' => 'Estrellas', 'years' => 7, 'months' => 2],
        'Emilia Cruz' => ['family' => 'Familia Cruz', 'environment' => 'Girasoles', 'years' => 4, 'months' => 6],
    ];

    /**
     * Correo de la familia de la demo => nombre de su Family.
     *
     * @return array<string, string>
     */
    private static function familias(): array
    {
        $familias = [];

        foreach (self::CUENTAS as $correo => $cuenta) {
            if ($cuenta['family'] !== null) {
                $familias[$correo] = $cuenta['family'];
            }
        }

        return $familias;
    }

    /**
     * Cuentas de la demo: correo => nombre, rol y familia.
     *
     * @return array<string, array{name: string, role: string, family: ?string}>
     */
    public static function cuentas(): array
    {
        return self::CUENTAS;
    }

    /**
     * @return list<string>
     */
    public static function correos(): array
    {
        return array_keys(self::CUENTAS);
    }

    /**
     * @return list<string>
     */
    public static function correosDocentes(): array
    {
        return [self::DOCENTE_GIRASOLES, self::DOCENTE_ESTRELLAS];
    }

    /**
     * @return list<string>
     */
    public static function nombresFamilias(): array
    {
        return array_values(self::familias());
    }

    /**
     * @return list<string>
     */
    public static function nombresAmbientes(): array
    {
        return array_keys(self::AMBIENTES);
    }

    /**
     * @return list<string>
     */
    public static function slugsTareas(): array
    {
        return array_column(self::tareas(), 'slug');
    }

    /**
     * Las seis tareas. `days` es la fecha límite respecto a hoy: una abierta
     * (+7), una que vence mañana (+1) y una vencida (-1) por ambiente.
     *
     * @return list<array{slug: string, title: string, environment: string, area: string, days: int, description: string, steps: list<string>}>
     */
    public static function tareas(): array
    {
        return [
            [
                'slug' => 'demo-mi-autorretrato-con-materiales-reciclados',
                'title' => 'Mi autorretrato con materiales reciclados',
                'environment' => 'Girasoles',
                'area' => 'Arte y Expresión',
                'days' => 7,
                'description' => 'Crea con tu hijo o hija un autorretrato usando materiales reciclados y compártenos una foto.',
                'steps' => [
                    'Reúnan materiales reciclados: cartón, tapas, botones, lana o revistas viejas.',
                    'Pídele que se mire en un espejo y nombre sus rasgos: ojos, nariz, boca y cabello.',
                    'Ayúdale a pegar los materiales sobre una hoja o un cartón hasta formar su cara.',
                    'Toma una foto del autorretrato terminado junto a tu hijo o hija.',
                    'Sube la foto y cuéntanos qué fue lo que más le gustó hacer.',
                ],
            ],
            [
                'slug' => 'demo-buscamos-figuras-geometricas-en-casa',
                'title' => 'Buscamos figuras geométricas en casa',
                'environment' => 'Girasoles',
                'area' => 'Matemática',
                'days' => 1,
                'description' => 'Encuentren círculos, cuadrados y triángulos en los objetos de la casa y fotografíenlos.',
                'steps' => [
                    'Explícale que hoy van a buscar figuras: círculo, cuadrado, triángulo y rectángulo.',
                    'Recorran la casa y señalen objetos con esas formas, como un plato, una ventana o un pañuelo doblado.',
                    'Pídele que diga el nombre de la figura de cada objeto.',
                    'Toma fotos de al menos tres objetos que hayan encontrado.',
                    'Sube las fotos y escribe cuál fue la figura más difícil de encontrar.',
                ],
            ],
            [
                'slug' => 'demo-dibujo-el-cuento-de-mi-familia',
                'title' => 'Dibujo: el cuento de mi familia',
                'environment' => 'Girasoles',
                'area' => 'Lectura',
                'days' => -1,
                'description' => 'Cuenta un cuento corto sobre la familia y pide a tu hijo o hija que dibuje la parte que más le gustó.',
                'steps' => [
                    'Siéntate con tu hijo o hija en un lugar tranquilo.',
                    'Cuéntale un cuento corto sobre su familia: quiénes son y qué les gusta hacer juntos.',
                    'Pídele que dibuje la parte del cuento que más le gustó.',
                    'Escribe, tal como lo diga, qué es lo que dibujó.',
                    'Toma una foto del dibujo y súbela con lo que escribiste.',
                ],
            ],
            [
                'slug' => 'demo-experimento-flota-o-se-hunde',
                'title' => 'Experimento: ¿flota o se hunde?',
                'environment' => 'Estrellas',
                'area' => 'Cultura y Naturaleza',
                'days' => 7,
                'description' => 'Hagan un experimento en casa para descubrir qué objetos flotan y cuáles se hunden.',
                'steps' => [
                    'Llenen un recipiente grande con agua.',
                    'Reúnan seis objetos de la casa: una cuchara, una tapa de plástico, una moneda, una hoja, una esponja y un corcho.',
                    'Antes de probar, pídele que adivine si cada objeto flota o se hunde y anótalo.',
                    'Coloquen cada objeto en el agua y registren qué pasó.',
                    'Toma una foto o un video corto del experimento y súbelo contándonos qué predicciones acertó.',
                ],
            ],
            [
                'slug' => 'demo-mi-receta-favorita-en-5-pasos',
                'title' => 'Mi receta favorita en 5 pasos',
                'environment' => 'Estrellas',
                'area' => 'Vida Práctica',
                'days' => 1,
                'description' => 'Escriban en cinco pasos una receta sencilla que le guste y prepárenla juntos.',
                'steps' => [
                    'Elige junto a tu hijo o hija una receta sencilla que le guste.',
                    'Escriban los ingredientes y pídele que los reúna sobre la mesa.',
                    'Pídele que explique en cinco pasos cómo se prepara y anótalos.',
                    'Prepárenla juntos; el uso del fuego y de los cuchillos queda siempre a cargo de un adulto.',
                    'Toma una foto del plato terminado y otra de la receta escrita, y súbelas.',
                ],
            ],
            [
                'slug' => 'demo-maqueta-del-sistema-solar',
                'title' => 'Maqueta del sistema solar',
                'environment' => 'Estrellas',
                'area' => 'Cultura y Naturaleza',
                'days' => -1,
                'description' => 'Construyan una maqueta del sistema solar con materiales que tengan en casa.',
                'steps' => [
                    'Consigan una base de cartón o una caja.',
                    'Con plastilina, papel o frutas pequeñas, hagan el Sol y los ocho planetas.',
                    'Ordénenlos según su distancia al Sol: Mercurio, Venus, Tierra, Marte, Júpiter, Saturno, Urano y Neptuno.',
                    'Rotula cada planeta con su nombre.',
                    'Toma una foto de la maqueta completa y súbela junto a un dato curioso que haya aprendido.',
                ],
            ],
        ];
    }

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('El escenario de grabación no se carga en producción: no se escribió nada.');

            return;
        }

        if (($conflicto = $this->conflicto()) !== null) {
            $this->command?->error($conflicto);

            return;
        }

        DB::transaction(function (): void {
            $docentes = $this->sembrarDocentes();
            $ambientes = $this->sembrarAmbientes($docentes);
            $familias = $this->sembrarFamilias();
            $this->sembrarNinos($familias, $ambientes);
            $this->sembrarTareas($ambientes);
        });

        $this->command?->info(sprintf(
            'Escenario de grabación listo: %d cuentas, %d ambientes, %d niños y %d tareas.',
            count(self::CUENTAS),
            count(self::AMBIENTES),
            count(self::NINOS),
            count(self::tareas()),
        ));
    }

    /**
     * Mensaje del primer dato existente que la demo no puede reutilizar sin
     * apropiarse de algo ajeno, o null si se puede sembrar. Se consulta antes
     * de escribir (y antes de limpiar) para no dejar la base a medias.
     */
    public function conflicto(): ?string
    {
        foreach (self::CUENTAS as $correo => $cuenta) {
            $usuario = User::query()->where('email', $correo)->first();

            if ($usuario !== null && $usuario->role !== $cuenta['role']) {
                return "La cuenta {$correo} ya existe con otro rol. No se tocó nada.";
            }
        }

        foreach (self::AMBIENTES as $nombre => $ambiente) {
            $docenteId = User::query()->where('email', $ambiente['teacher'])->value('id');

            foreach (Environment::query()->where('name', $nombre)->get() as $existente) {
                if ($docenteId === null || (int) $existente->teacher_id !== (int) $docenteId) {
                    return "Ya existe un ambiente llamado «{$nombre}» que no es de la demo. No se tocó nada.";
                }
            }
        }

        foreach (self::familias() as $correo => $nombre) {
            $usuario = User::query()->where('email', $correo)->first();

            if ($usuario !== null && $usuario->family?->name !== $nombre) {
                return "La cuenta {$correo} existe, pero no pertenece a «{$nombre}». No se tocó nada.";
            }

            foreach (Family::query()->where('name', $nombre)->get() as $existente) {
                if ($usuario === null || (int) $usuario->family_id !== $existente->id) {
                    return "Ya existe una familia llamada «{$nombre}» que no es de la demo. No se tocó nada.";
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, User> correo => docente
     */
    private function sembrarDocentes(): array
    {
        $docentes = [];

        foreach (self::correosDocentes() as $correo) {
            $docentes[$correo] = $this->sembrarUsuario($correo, self::CUENTAS[$correo]);
        }

        return $docentes;
    }

    /**
     * @param  array{name: string, role: string, family: ?string}  $cuenta
     */
    private function sembrarUsuario(string $correo, array $cuenta, ?Family $familia = null): User
    {
        $usuario = User::query()->where('email', $correo)->first();

        if ($usuario !== null) {
            if (! $usuario->active) {
                $usuario->forceFill(['active' => true])->save();
            }

            return $usuario;
        }

        $fabrica = $cuenta['role'] === User::ROLE_GUIA ? User::factory()->guia() : User::factory()->familia($familia);

        return $fabrica->create([
            'name' => $cuenta['name'],
            'email' => $correo,
            'password' => Hash::make(self::PASSWORD),
            'active' => true,
        ]);
    }

    /**
     * @param  array<string, User>  $docentes
     * @return array<string, Environment> nombre => ambiente
     */
    private function sembrarAmbientes(array $docentes): array
    {
        $ambientes = [];

        foreach (self::AMBIENTES as $nombre => $datos) {
            $ambientes[$nombre] = Environment::query()->updateOrCreate(
                ['name' => $nombre, 'teacher_id' => $docentes[$datos['teacher']]->id],
                [
                    'age_range' => $datos['age_range'],
                    'stage' => $datos['stage'],
                    'description' => 'Ambiente de demostración para la grabación del flujo de tareas.',
                    'active' => true,
                    'order' => $datos['order'],
                ],
            );
        }

        return $ambientes;
    }

    /**
     * La familia se resuelve por su cuenta de acceso, no por nombre suelto:
     * así nunca se adopta una familia real que se llame igual.
     *
     * @return array<string, Family> nombre => familia
     */
    private function sembrarFamilias(): array
    {
        $familias = [];

        foreach (self::familias() as $correo => $nombre) {
            $existente = User::query()->where('email', $correo)->first();

            $familia = $existente?->family ?? Family::query()->create(['name' => $nombre]);
            $this->sembrarUsuario($correo, self::CUENTAS[$correo], $familia);

            $familias[$nombre] = $familia;
        }

        return $familias;
    }

    /**
     * @param  array<string, Family>  $familias
     * @param  array<string, Environment>  $ambientes
     */
    private function sembrarNinos(array $familias, array $ambientes): void
    {
        foreach (self::NINOS as $nombre => $datos) {
            $familia = $familias[$datos['family']];
            $nino = Child::withTrashed()->where('family_id', $familia->id)->where('name', $nombre)->first() ?? new Child;

            if ($nino->trashed()) {
                $nino->restore();
            }

            $nino->fill([
                'family_id' => $familia->id,
                'environment_id' => $ambientes[$datos['environment']]->id,
                'name' => $nombre,
                'status' => 'active',
            ]);

            // La fecha de nacimiento solo se fija al crearlo: no envejece en cada corrida.
            if (! $nino->exists) {
                $nino->birth_date = now()->subYears($datos['years'])->subMonths($datos['months'])->toDateString();
            }

            $nino->save();
        }
    }

    /**
     * Crea o repone cada tarea sin disparar eventos: volver a "publicado" una
     * tarea que alguien despublicó no debe mandar avisos a las familias.
     *
     * @param  array<string, Environment>  $ambientes
     */
    private function sembrarTareas(array $ambientes): void
    {
        foreach (self::tareas() as $datos) {
            $ambiente = $ambientes[$datos['environment']];
            $tarea = Content::withTrashed()->where('slug', $datos['slug'])->first() ?? new Content;

            if ($tarea->trashed()) {
                $tarea->restoreQuietly();
            }

            $tarea->fill([
                'title' => $datos['title'],
                'slug' => $datos['slug'],
                'type' => Content::TYPE_TASK,
                'description' => $datos['description'],
                'body' => $this->cuerpo($datos['steps']),
                'environment_id' => $ambiente->id,
                'area_id' => Area::query()->where('name', $datos['area'])->value('id'),
                'teacher_id' => $ambiente->teacher_id,
                'requires_evidence' => true,
                'status' => Content::STATUS_PUBLISHED,
                'published_at' => now()->subDay(),
                'due_date' => today()->addDays($datos['days'])->toDateString(),
            ])->saveQuietly();
        }
    }

    /**
     * Cuerpo en HTML (lo guarda así el editor del panel) con los pasos numerados.
     *
     * @param  list<string>  $pasos
     */
    private function cuerpo(array $pasos): string
    {
        $items = implode('', array_map(fn (string $paso): string => '<li>'.e($paso).'</li>', $pasos));

        return "<p>Sigue estos pasos en casa con tu hijo o hija:</p><ol>{$items}</ol>";
    }
}

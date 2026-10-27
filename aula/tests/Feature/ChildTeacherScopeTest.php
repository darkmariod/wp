<?php

namespace Tests\Feature;

use App\Filament\Pages\ReporteAsistencia;
use App\Filament\Resources\ChildResource;
use App\Filament\Resources\ChildResource\Pages\CreateChild;
use App\Filament\Resources\ChildResource\Pages\ListChildren;
use App\Filament\Resources\EnvironmentResource\Pages\ListEnvironments;
use App\Filament\Widgets\AsistenciaDelMes;
use App\Filament\Widgets\EstadisticasGenerales;
use App\Filament\Widgets\EvidenciasPorEstado;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\Environment;
use App\Models\Evidence;
use App\Models\Family;
use App\Models\User;
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Una docente (rol guía) solo ve a los niños de su propio grado, es decir,
 * de los ambientes donde ella es la docente. El personal (administración y
 * coordinación) sigue viéndolo todo. Un niño sin ambiente solo lo ve el
 * personal.
 */
class ChildTeacherScopeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Dos ambientes con su docente, una docente sin ambiente, el personal,
     * y niños repartidos entre ambos grados (más uno sin ambiente).
     */
    private function escenario(): array
    {
        $docenteA = User::factory()->guia()->create(['name' => 'Docente A']);
        $docenteB = User::factory()->guia()->create(['name' => 'Docente B']);
        $docenteSinAmbiente = User::factory()->guia()->create(['name' => 'Docente Libre']);
        $administrador = User::factory()->administrador()->create();
        $coordinacion = User::factory()->coordinacion()->create();

        $ambienteA = Environment::factory()->create(['name' => 'Ambiente Alfa', 'teacher_id' => $docenteA->id]);
        $ambienteB = Environment::factory()->razonadoras()->create(['name' => 'Ambiente Beta', 'teacher_id' => $docenteB->id]);
        // Un ambiente sin docente asignada no le pertenece a nadie.
        $ambienteHuerfano = Environment::factory()->create(['name' => 'Ambiente Huérfano', 'teacher_id' => null]);

        $familiaA1 = Family::factory()->create(['name' => 'Familia Alvarado']);
        $familiaA2 = Family::factory()->create(['name' => 'Familia Ayala']);
        $familiaB = Family::factory()->create(['name' => 'Familia Benítez']);
        $familiaMixta = Family::factory()->create(['name' => 'Familia Mixta']);
        $familiaSin = Family::factory()->create(['name' => 'Familia Silva']);
        $familiaVacia = Family::factory()->create(['name' => 'Familia Vacía']);
        $familiaHuerfana = Family::factory()->create(['name' => 'Familia Huérfana']);

        $ninoA1 = Child::factory()->create(['name' => 'Ana Alvarado', 'family_id' => $familiaA1->id, 'environment_id' => $ambienteA->id]);
        $ninoA2 = Child::factory()->create(['name' => 'Andrés Ayala', 'family_id' => $familiaA2->id, 'environment_id' => $ambienteA->id]);
        $ninoMixtoA = Child::factory()->create(['name' => 'Amalia Mixta', 'family_id' => $familiaMixta->id, 'environment_id' => $ambienteA->id]);
        $ninoB1 = Child::factory()->create(['name' => 'Bruno Benítez', 'family_id' => $familiaB->id, 'environment_id' => $ambienteB->id]);
        $ninoB2 = Child::factory()->create(['name' => 'Bea Benítez', 'family_id' => $familiaB->id, 'environment_id' => $ambienteB->id]);
        $ninoMixtoB = Child::factory()->create(['name' => 'Blas Mixto', 'family_id' => $familiaMixta->id, 'environment_id' => $ambienteB->id]);
        $ninoSinAmbiente = Child::factory()->create(['name' => 'Sofía Silva', 'family_id' => $familiaSin->id, 'environment_id' => null]);
        $ninoHuerfano = Child::factory()->create(['name' => 'Hugo Huérfano', 'family_id' => $familiaHuerfana->id, 'environment_id' => $ambienteHuerfano->id]);

        // Un niño dado de baja del grado A no debe reaparecer.
        $ninoEliminado = Child::factory()->create(['name' => 'Elena Eliminada', 'family_id' => $familiaA1->id, 'environment_id' => $ambienteA->id]);
        $ninoEliminado->delete();

        return compact(
            'docenteA', 'docenteB', 'docenteSinAmbiente', 'administrador', 'coordinacion',
            'ambienteA', 'ambienteB', 'ambienteHuerfano',
            'familiaA1', 'familiaA2', 'familiaB', 'familiaMixta', 'familiaSin', 'familiaVacia', 'familiaHuerfana',
            'ninoA1', 'ninoA2', 'ninoMixtoA', 'ninoB1', 'ninoB2', 'ninoMixtoB', 'ninoSinAmbiente', 'ninoHuerfano', 'ninoEliminado',
        );
    }

    /**
     * Lo que hay que dejar pasar y lo que no, para no repetir listas.
     */
    private function ninosDelGradoA(array $e): array
    {
        return [$e['ninoA1'], $e['ninoA2'], $e['ninoMixtoA']];
    }

    private function ninosDelGradoB(array $e): array
    {
        return [$e['ninoB1'], $e['ninoB2'], $e['ninoMixtoB']];
    }

    private function ninosFueraDeAmbientes(array $e): array
    {
        return [$e['ninoSinAmbiente'], $e['ninoHuerfano']];
    }

    /**
     * Llama a un método protegido de un widget (getStats / getData).
     */
    private function invocar(object $objeto, string $metodo): mixed
    {
        return (new ReflectionMethod($objeto, $metodo))->invoke($objeto);
    }

    /**
     * Opciones que ve el usuario en un selector del formulario de niños.
     */
    private function opcionesDelFormulario(User $usuario, string $campo): array
    {
        $opciones = [];

        Livewire::actingAs($usuario)
            ->test(CreateChild::class)
            ->assertFormFieldExists($campo, function (Select $select) use (&$opciones): bool {
                $opciones = $select->getOptions();

                return true;
            });

        return $opciones;
    }

    /**
     * Opciones de un filtro de la tabla de niños para ese usuario.
     */
    private function opcionesDelFiltro(User $usuario, string $filtro): array
    {
        $campos = Livewire::actingAs($usuario)
            ->test(ListChildren::class)
            ->instance()
            ->getTableFiltersForm()
            ->getFlatFields(withHidden: true);

        $select = collect($campos)->first(fn ($campo, $clave) => str_starts_with($clave, $filtro.'.'));

        $this->assertInstanceOf(Select::class, $select);

        return $select->getOptions();
    }

    public function test_la_docente_a_ve_exactamente_los_ninos_de_su_ambiente_en_el_listado(): void
    {
        $e = $this->escenario();

        Livewire::actingAs($e['docenteA'])
            ->test(ListChildren::class)
            ->assertCanSeeTableRecords($this->ninosDelGradoA($e))
            ->assertCanNotSeeTableRecords([
                ...$this->ninosDelGradoB($e),
                ...$this->ninosFueraDeAmbientes($e),
            ])
            ->assertCountTableRecords(3);
    }

    public function test_la_docente_b_ve_exactamente_los_ninos_de_su_ambiente_en_el_listado(): void
    {
        $e = $this->escenario();

        Livewire::actingAs($e['docenteB'])
            ->test(ListChildren::class)
            ->assertCanSeeTableRecords($this->ninosDelGradoB($e))
            ->assertCanNotSeeTableRecords([
                ...$this->ninosDelGradoA($e),
                ...$this->ninosFueraDeAmbientes($e),
            ])
            ->assertCountTableRecords(3);
    }

    public function test_el_listado_de_la_docente_no_muestra_los_ninos_eliminados(): void
    {
        $e = $this->escenario();

        Livewire::actingAs($e['docenteA'])
            ->test(ListChildren::class)
            ->assertCanNotSeeTableRecords([$e['ninoEliminado']]);
    }

    public function test_la_docente_sin_ambiente_no_ve_ningun_nino(): void
    {
        $e = $this->escenario();

        Livewire::actingAs($e['docenteSinAmbiente'])
            ->test(ListChildren::class)
            ->assertCountTableRecords(0)
            ->assertCanNotSeeTableRecords([
                ...$this->ninosDelGradoA($e),
                ...$this->ninosDelGradoB($e),
                ...$this->ninosFueraDeAmbientes($e),
            ]);
    }

    public function test_el_nino_sin_ambiente_solo_lo_ve_el_personal(): void
    {
        $e = $this->escenario();

        foreach (['docenteA', 'docenteB', 'docenteSinAmbiente'] as $docente) {
            Livewire::actingAs($e[$docente])
                ->test(ListChildren::class)
                ->assertCanNotSeeTableRecords([$e['ninoSinAmbiente']]);
        }

        foreach (['administrador', 'coordinacion'] as $personal) {
            Livewire::actingAs($e[$personal])
                ->test(ListChildren::class)
                ->assertCanSeeTableRecords([$e['ninoSinAmbiente']]);
        }
    }

    public function test_administracion_y_coordinacion_ven_a_todos_los_ninos(): void
    {
        $e = $this->escenario();

        $todos = [
            ...$this->ninosDelGradoA($e),
            ...$this->ninosDelGradoB($e),
            ...$this->ninosFueraDeAmbientes($e),
        ];

        foreach (['administrador', 'coordinacion'] as $personal) {
            Livewire::actingAs($e[$personal])
                ->test(ListChildren::class)
                ->assertCanSeeTableRecords($todos)
                ->assertCanNotSeeTableRecords([$e['ninoEliminado']])
                ->assertCountTableRecords(8);
        }
    }

    public function test_la_docente_no_puede_abrir_la_ficha_de_un_nino_de_otro_grado(): void
    {
        $e = $this->escenario();

        $urlAjena = ChildResource::getUrl('edit', ['record' => $e['ninoB1']]);

        // El niño ajeno ni siquiera se resuelve: no se filtra que existe.
        $this->actingAs($e['docenteA'])->get($urlAjena)->assertNotFound();
        $this->actingAs($e['docenteSinAmbiente'])->get($urlAjena)->assertNotFound();
        $this->actingAs($e['docenteA'])
            ->get(ChildResource::getUrl('edit', ['record' => $e['ninoSinAmbiente']]))
            ->assertNotFound();
    }

    public function test_la_docente_solo_puede_ver_a_los_ninos_de_su_grado_segun_el_recurso(): void
    {
        $e = $this->escenario();

        $this->actingAs($e['docenteA']);

        $this->assertTrue(ChildResource::canView($e['ninoA1']));
        $this->assertFalse(ChildResource::canView($e['ninoB1']));
        $this->assertFalse(ChildResource::canView($e['ninoSinAmbiente']));
        $this->assertNull(ChildResource::getEloquentQuery()->whereKey($e['ninoB1']->id)->first());
        $this->assertNotNull(ChildResource::getEloquentQuery()->whereKey($e['ninoA1']->id)->first());
    }

    public function test_el_personal_si_puede_abrir_la_ficha_de_cualquier_nino(): void
    {
        $e = $this->escenario();

        foreach (['administrador', 'coordinacion'] as $personal) {
            foreach (['ninoA1', 'ninoB1', 'ninoSinAmbiente'] as $nino) {
                $this->actingAs($e[$personal])
                    ->get(ChildResource::getUrl('edit', ['record' => $e[$nino]]))
                    ->assertOk();
            }
        }
    }

    public function test_el_filtro_de_familia_de_la_docente_solo_incluye_familias_con_ninos_en_su_grado(): void
    {
        $e = $this->escenario();

        $opciones = $this->opcionesDelFiltro($e['docenteA'], 'family_id');

        $this->assertEqualsCanonicalizing(
            [$e['familiaA1']->id, $e['familiaA2']->id, $e['familiaMixta']->id],
            array_keys($opciones),
        );
        $this->assertArrayNotHasKey($e['familiaB']->id, $opciones);
        $this->assertArrayNotHasKey($e['familiaSin']->id, $opciones);
        $this->assertArrayNotHasKey($e['familiaVacia']->id, $opciones);
        $this->assertArrayNotHasKey($e['familiaHuerfana']->id, $opciones);
    }

    public function test_el_filtro_de_familia_de_la_docente_sin_ambiente_esta_vacio(): void
    {
        $e = $this->escenario();

        $this->assertSame([], $this->opcionesDelFiltro($e['docenteSinAmbiente'], 'family_id'));
    }

    public function test_el_filtro_de_familia_del_personal_incluye_todas_las_familias(): void
    {
        $e = $this->escenario();

        $opciones = $this->opcionesDelFiltro($e['administrador'], 'family_id');

        $this->assertCount(Family::count(), $opciones);
        $this->assertArrayHasKey($e['familiaVacia']->id, $opciones);
        $this->assertArrayHasKey($e['familiaB']->id, $opciones);
    }

    public function test_el_filtro_de_ambiente_de_la_docente_solo_incluye_sus_ambientes(): void
    {
        $e = $this->escenario();

        $this->assertSame(
            [$e['ambienteA']->id => 'Ambiente Alfa'],
            $this->opcionesDelFiltro($e['docenteA'], 'environment_id'),
        );
        $this->assertSame(
            [$e['ambienteB']->id => 'Ambiente Beta'],
            $this->opcionesDelFiltro($e['docenteB'], 'environment_id'),
        );
        $this->assertSame([], $this->opcionesDelFiltro($e['docenteSinAmbiente'], 'environment_id'));
    }

    public function test_el_filtro_de_ambiente_del_personal_incluye_todos_los_ambientes(): void
    {
        $e = $this->escenario();

        $opciones = $this->opcionesDelFiltro($e['coordinacion'], 'environment_id');

        $this->assertEqualsCanonicalizing(
            [$e['ambienteA']->id, $e['ambienteB']->id, $e['ambienteHuerfano']->id],
            array_keys($opciones),
        );
    }

    public function test_el_selector_de_familia_del_formulario_de_la_docente_excluye_familias_de_otros_grados(): void
    {
        $e = $this->escenario();

        $opciones = $this->opcionesDelFormulario($e['docenteA'], 'family_id');

        // Las familias de su grado y las que aún no tienen ningún niño (para
        // poder anotar al primer hijo de una familia nueva); ninguna con niños
        // solo de otros grados o sin ambiente.
        $this->assertEqualsCanonicalizing(
            [$e['familiaA1']->id, $e['familiaA2']->id, $e['familiaMixta']->id, $e['familiaVacia']->id],
            array_keys($opciones),
        );
        $this->assertArrayNotHasKey($e['familiaB']->id, $opciones);
        $this->assertArrayNotHasKey($e['familiaSin']->id, $opciones);
        $this->assertArrayNotHasKey($e['familiaHuerfana']->id, $opciones);
    }

    public function test_el_selector_de_familia_del_formulario_del_personal_incluye_todas_las_familias(): void
    {
        $e = $this->escenario();

        $opciones = $this->opcionesDelFormulario($e['administrador'], 'family_id');

        $this->assertCount(Family::count(), $opciones);
        $this->assertArrayHasKey($e['familiaVacia']->id, $opciones);
    }

    public function test_el_selector_de_ambiente_del_formulario_solo_incluye_los_de_la_docente(): void
    {
        $e = $this->escenario();

        $this->assertSame(
            [$e['ambienteA']->id => 'Ambiente Alfa'],
            $this->opcionesDelFormulario($e['docenteA'], 'environment_id'),
        );
        $this->assertSame([], $this->opcionesDelFormulario($e['docenteSinAmbiente'], 'environment_id'));
    }

    public function test_el_selector_de_ambiente_del_formulario_del_personal_incluye_todos(): void
    {
        $e = $this->escenario();

        $opciones = $this->opcionesDelFormulario($e['administrador'], 'environment_id');

        $this->assertEqualsCanonicalizing(
            [$e['ambienteA']->id, $e['ambienteB']->id, $e['ambienteHuerfano']->id],
            array_keys($opciones),
        );
    }

    public function test_la_docente_no_puede_anotar_un_nino_en_una_familia_sin_ninos_en_su_grado(): void
    {
        $e = $this->escenario();

        Livewire::actingAs($e['docenteA'])
            ->test(CreateChild::class)
            ->fillForm([
                'name' => 'Intento familia ajena',
                'family_id' => $e['familiaB']->id,
                'environment_id' => $e['ambienteA']->id,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasFormErrors(['family_id']);

        $this->assertDatabaseMissing('children', ['name' => 'Intento familia ajena']);
    }

    public function test_la_docente_si_puede_anotar_al_primer_hijo_de_una_familia_sin_ninos_todavia(): void
    {
        $e = $this->escenario();

        Livewire::actingAs($e['docenteA'])
            ->test(CreateChild::class)
            ->fillForm([
                'name' => 'Primer hijo',
                'family_id' => $e['familiaVacia']->id,
                'environment_id' => $e['ambienteA']->id,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('children', ['name' => 'Primer hijo', 'family_id' => $e['familiaVacia']->id]);
    }

    public function test_la_docente_si_puede_anotar_un_hermano_en_una_familia_con_ninos_en_su_grado(): void
    {
        $e = $this->escenario();

        Livewire::actingAs($e['docenteA'])
            ->test(CreateChild::class)
            ->fillForm([
                'name' => 'Hermano nuevo',
                'family_id' => $e['familiaMixta']->id,
                'environment_id' => $e['ambienteA']->id,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('children', ['name' => 'Hermano nuevo', 'environment_id' => $e['ambienteA']->id]);
    }

    public function test_el_alcance_taught_by_deja_pasar_segun_el_rol(): void
    {
        $e = $this->escenario();

        $idsPara = fn (?User $usuario): array => Child::query()->taughtBy($usuario)->pluck('id')->all();

        $this->assertCount(8, $idsPara($e['administrador']));
        $this->assertCount(8, $idsPara($e['coordinacion']));

        $this->assertEqualsCanonicalizing(
            collect($this->ninosDelGradoA($e))->pluck('id')->all(),
            $idsPara($e['docenteA']),
        );
        $this->assertEqualsCanonicalizing(
            collect($this->ninosDelGradoB($e))->pluck('id')->all(),
            $idsPara($e['docenteB']),
        );
        $this->assertSame([], $idsPara($e['docenteSinAmbiente']));

        $familia = User::factory()->familia($e['familiaA1'])->create();
        $estudiante = User::factory()->estudiante()->create();

        $this->assertSame([], $idsPara($familia));
        $this->assertSame([], $idsPara($estudiante));
        $this->assertSame([], $idsPara(null));
    }

    public function test_el_alcance_taught_by_se_combina_con_otras_condiciones(): void
    {
        $e = $this->escenario();

        $e['ninoA2']->update(['status' => 'inactive']);

        $activos = Child::query()->where('status', 'active')->taughtBy($e['docenteA'])->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$e['ninoA1']->id, $e['ninoMixtoA']->id], $activos);
    }

    public function test_el_alcance_de_familias_con_ninos_de_la_docente(): void
    {
        $e = $this->escenario();

        $idsPara = fn (?User $usuario): array => Family::query()->withChildrenTaughtBy($usuario)->pluck('id')->all();

        $this->assertCount(Family::count(), $idsPara($e['administrador']));
        $this->assertEqualsCanonicalizing(
            [$e['familiaA1']->id, $e['familiaA2']->id, $e['familiaMixta']->id],
            $idsPara($e['docenteA']),
        );
        $this->assertEqualsCanonicalizing(
            [$e['familiaB']->id, $e['familiaMixta']->id],
            $idsPara($e['docenteB']),
        );
        $this->assertSame([], $idsPara($e['docenteSinAmbiente']));
        $this->assertSame([], $idsPara(null));

        // Con la opción de incluir familias sin niños (selector del formulario).
        $conVacias = Family::query()->withChildrenTaughtBy($e['docenteA'], true)->pluck('id')->all();
        $this->assertEqualsCanonicalizing(
            [$e['familiaA1']->id, $e['familiaA2']->id, $e['familiaMixta']->id, $e['familiaVacia']->id],
            $conVacias,
        );
        $this->assertSame([], Family::query()->withChildrenTaughtBy(null, true)->whereHas('children')->pluck('id')->all());
    }

    public function test_el_reporte_de_asistencia_no_lista_ninos_de_otro_grado_aunque_se_fuerce_el_ambiente(): void
    {
        $e = $this->escenario();

        $opciones = fn (User $usuario, Environment $ambiente): array => Livewire::actingAs($usuario)
            ->test(ReporteAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->instance()
            ->opcionesNinos();

        // Su propio grado: sus niños. El de otra docente: nada.
        $this->assertEqualsCanonicalizing(
            collect($this->ninosDelGradoA($e))->pluck('id')->all(),
            array_keys($opciones($e['docenteA'], $e['ambienteA'])),
        );
        $this->assertSame([], $opciones($e['docenteA'], $e['ambienteB']));

        // El personal sigue viendo el grado que elija.
        $this->assertEqualsCanonicalizing(
            collect($this->ninosDelGradoB($e))->pluck('id')->all(),
            array_keys($opciones($e['administrador'], $e['ambienteB'])),
        );
    }

    public function test_el_listado_de_ambientes_oculta_el_total_de_ninos_a_la_docente(): void
    {
        $e = $this->escenario();

        Livewire::actingAs($e['docenteA'])
            ->test(ListEnvironments::class)
            ->assertTableColumnHidden('children.count');

        Livewire::actingAs($e['administrador'])
            ->test(ListEnvironments::class)
            ->assertTableColumnVisible('children.count');
    }

    /**
     * Asistencias y evidencias de ambos grados para probar los widgets.
     */
    private function datosParaWidgets(array $e): void
    {
        $hoy = now()->toDateString();

        foreach ([
            [$e['ninoA1'], Attendance::STATUS_PRESENTE],
            [$e['ninoA2'], Attendance::STATUS_ATRASO],
            [$e['ninoB1'], Attendance::STATUS_PRESENTE],
            [$e['ninoB2'], Attendance::STATUS_PRESENTE],
            [$e['ninoMixtoB'], Attendance::STATUS_FALTA_JUSTIFICADA],
            [$e['ninoSinAmbiente'], Attendance::STATUS_PRESENTE],
        ] as [$nino, $estado]) {
            Attendance::factory()->create(['child_id' => $nino->id, 'date' => $hoy, 'status' => $estado]);
        }

        foreach ([
            [$e['ninoA1'], Evidence::STATUS_SUBMITTED],
            [$e['ninoA2'], Evidence::STATUS_RESPONDED],
            [$e['ninoB1'], Evidence::STATUS_SUBMITTED],
            [$e['ninoB2'], Evidence::STATUS_PENDING],
            [$e['ninoSinAmbiente'], Evidence::STATUS_SUBMITTED],
        ] as [$nino, $estado]) {
            Evidence::factory()->create([
                'child_id' => $nino->id,
                'family_id' => $nino->family_id,
                'status' => $estado,
                'reviewed_at' => $estado === Evidence::STATUS_RESPONDED ? now() : null,
            ]);
        }
    }

    /**
     * Valores de las cuatro tarjetas de EstadisticasGenerales para ese usuario.
     */
    private function tarjetas(User $usuario): array
    {
        $widget = Livewire::actingAs($usuario)->test(EstadisticasGenerales::class)->instance();

        return collect($this->invocar($widget, 'getStats'))
            ->mapWithKeys(fn ($stat) => [(string) $stat->getLabel() => (int) $stat->getValue()])
            ->all();
    }

    public function test_las_estadisticas_generales_de_la_docente_solo_cuentan_su_grado(): void
    {
        $e = $this->escenario();
        $this->datosParaWidgets($e);

        $this->assertSame([
            'Niños activos' => 3,
            'Asistencias hoy' => 2,
            'Evidencias por revisar' => 1,
            'Evidencias respondidas (mes)' => 1,
        ], $this->tarjetas($e['docenteA']));

        $this->assertSame([
            'Niños activos' => 3,
            'Asistencias hoy' => 3,
            'Evidencias por revisar' => 2,
            'Evidencias respondidas (mes)' => 0,
        ], $this->tarjetas($e['docenteB']));

        $this->assertSame([
            'Niños activos' => 0,
            'Asistencias hoy' => 0,
            'Evidencias por revisar' => 0,
            'Evidencias respondidas (mes)' => 0,
        ], $this->tarjetas($e['docenteSinAmbiente']));
    }

    public function test_las_estadisticas_generales_del_personal_cuentan_todo(): void
    {
        $e = $this->escenario();
        $this->datosParaWidgets($e);

        $this->assertSame([
            'Niños activos' => 8,
            'Asistencias hoy' => 6,
            'Evidencias por revisar' => 4,
            'Evidencias respondidas (mes)' => 1,
        ], $this->tarjetas($e['administrador']));
    }

    public function test_la_asistencia_del_mes_de_la_docente_solo_cuenta_su_grado(): void
    {
        $e = $this->escenario();
        $this->datosParaWidgets($e);

        $datos = fn (User $usuario): array => $this->invocar(
            Livewire::actingAs($usuario)->test(AsistenciaDelMes::class)->instance(),
            'getData',
        )['datasets'][0]['data'];

        // Presente, atraso, justificada, injustificada.
        $this->assertSame([1, 1, 0, 0], $datos($e['docenteA']));
        $this->assertSame([2, 0, 1, 0], $datos($e['docenteB']));
        $this->assertSame([0, 0, 0, 0], $datos($e['docenteSinAmbiente']));
        $this->assertSame([4, 1, 1, 0], $datos($e['administrador']));
    }

    public function test_las_evidencias_por_estado_de_la_docente_solo_cuentan_su_grado(): void
    {
        $e = $this->escenario();
        $this->datosParaWidgets($e);

        $datos = fn (User $usuario): array => $this->invocar(
            Livewire::actingAs($usuario)->test(EvidenciasPorEstado::class)->instance(),
            'getData',
        )['datasets'][0]['data'];

        // Pendiente, enviada, vista, respondida.
        $this->assertSame([0, 1, 0, 1], $datos($e['docenteA']));
        $this->assertSame([1, 1, 0, 0], $datos($e['docenteB']));
        $this->assertSame([0, 0, 0, 0], $datos($e['docenteSinAmbiente']));
        $this->assertSame([1, 3, 0, 1], $datos($e['coordinacion']));
    }
}

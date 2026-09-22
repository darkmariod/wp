<?php

namespace Tests\Feature;

use App\Filament\Pages\RegistrarAsistencia;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\Environment;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Registro diario de asistencia: es la pantalla que la docente usa
 * todos los días, y la más expuesta a manipulación de la request (el
 * estado de cada niño viaja como un array plano en el payload de
 * Livewire). Cada guardia acá es una línea de defensa contra un
 * environment_id o child_id ajenos, o un estado inventado.
 */
class RegistrarAsistenciaTest extends TestCase
{
    use RefreshDatabase;

    private function ambienteConNinos(int $cantidad = 2): array
    {
        $guia = User::factory()->guia()->create();
        $ambiente = Environment::factory()->create(['teacher_id' => $guia->id]);
        $ninos = Child::factory()->count($cantidad)->create(['environment_id' => $ambiente->id]);

        return compact('guia', 'ambiente', 'ninos');
    }

    public function test_solo_staff_y_guia_acceden_al_registro(): void
    {
        $staff = User::factory()->administrador()->create();
        $this->actingAs($staff);
        $this->assertTrue(RegistrarAsistencia::canAccess());

        $guia = User::factory()->guia()->create();
        $this->actingAs($guia);
        $this->assertTrue(RegistrarAsistencia::canAccess());

        $familia = User::factory()->familia(Family::factory()->create())->create();
        $this->actingAs($familia);
        $this->assertFalse(RegistrarAsistencia::canAccess());
    }

    public function test_la_guia_no_ve_ambientes_ajenos_en_el_selector(): void
    {
        ['guia' => $guia] = $this->ambienteConNinos();
        $otraGuia = User::factory()->guia()->create();
        $ambienteAjeno = Environment::factory()->create(['teacher_id' => $otraGuia->id]);

        $this->actingAs($guia);

        $page = new RegistrarAsistencia;
        $this->assertArrayNotHasKey($ambienteAjeno->id, $page->opcionesAmbientes());
    }

    public function test_guarda_los_estados_marcados(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente, 'ninos' => $ninos] = $this->ambienteConNinos();

        Livewire::actingAs($guia)
            ->test(RegistrarAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('fecha', '2026-09-21')
            ->set("estados.{$ninos[0]->id}", Attendance::STATUS_PRESENTE)
            ->set("estados.{$ninos[1]->id}", Attendance::STATUS_ATRASO)
            ->call('guardar');

        $this->assertDatabaseHas('attendances', [
            'child_id' => $ninos[0]->id,
            'date' => '2026-09-21 00:00:00',
            'status' => Attendance::STATUS_PRESENTE,
        ]);
        $this->assertDatabaseHas('attendances', [
            'child_id' => $ninos[1]->id,
            'date' => '2026-09-21 00:00:00',
            'status' => Attendance::STATUS_ATRASO,
        ]);
    }

    public function test_guardar_dos_veces_el_mismo_dia_corrige_en_vez_de_duplicar(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente, 'ninos' => $ninos] = $this->ambienteConNinos(1);

        Livewire::actingAs($guia)
            ->test(RegistrarAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('fecha', '2026-09-21')
            ->set("estados.{$ninos[0]->id}", Attendance::STATUS_ATRASO)
            ->call('guardar');

        // La guía se equivocó y corrige el mismo día: tiene que quedar
        // un solo registro actualizado, no dos.
        Livewire::actingAs($guia)
            ->test(RegistrarAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('fecha', '2026-09-21')
            ->set("estados.{$ninos[0]->id}", Attendance::STATUS_PRESENTE)
            ->call('guardar');

        $this->assertSame(1, Attendance::where('child_id', $ninos[0]->id)->count());
        $this->assertDatabaseHas('attendances', [
            'child_id' => $ninos[0]->id,
            'date' => '2026-09-21 00:00:00',
            'status' => Attendance::STATUS_PRESENTE,
        ]);
    }

    public function test_sin_marcar_a_nadie_no_guarda_nada_y_avisa(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente] = $this->ambienteConNinos();

        Livewire::actingAs($guia)
            ->test(RegistrarAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('fecha', '2026-09-21')
            ->call('guardar')
            ->assertNotified('No marcaste ningún niño');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_un_estado_inventado_se_descarta_sin_romper_el_guardado(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente, 'ninos' => $ninos] = $this->ambienteConNinos(2);

        Livewire::actingAs($guia)
            ->test(RegistrarAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('fecha', '2026-09-21')
            ->set("estados.{$ninos[0]->id}", 'de_vacaciones') // no es un estado válido
            ->set("estados.{$ninos[1]->id}", Attendance::STATUS_PRESENTE)
            ->call('guardar');

        $this->assertDatabaseMissing('attendances', ['child_id' => $ninos[0]->id]);
        $this->assertDatabaseHas('attendances', [
            'child_id' => $ninos[1]->id,
            'status' => Attendance::STATUS_PRESENTE,
        ]);
    }

    public function test_la_guia_no_puede_marcar_asistencia_en_un_ambiente_ajeno(): void
    {
        ['guia' => $guia] = $this->ambienteConNinos();
        $otraGuia = User::factory()->guia()->create();
        $ambienteAjeno = Environment::factory()->create(['teacher_id' => $otraGuia->id]);
        $ninoAjeno = Child::factory()->create(['environment_id' => $ambienteAjeno->id]);

        // Manipula el environment_id como si fuera el propio, apuntando
        // al ambiente de otra guía. Ya el propio Select de Filament lo
        // rechaza como opción inválida (no está en opcionesAmbientes());
        // el chequeo explícito en guardar() es la segunda línea de
        // defensa si esa validación alguna vez dejara de aplicarse.
        Livewire::actingAs($guia)
            ->test(RegistrarAsistencia::class)
            ->set('environment_id', $ambienteAjeno->id)
            ->set('fecha', '2026-09-21')
            ->set("estados.{$ninoAjeno->id}", Attendance::STATUS_PRESENTE)
            ->call('guardar')
            ->assertHasErrors(['environment_id']);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_un_child_id_inyectado_de_otro_ambiente_no_se_guarda(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente] = $this->ambienteConNinos(1);
        $ninoAjeno = Child::factory()->create(); // en otro ambiente, sin relación con esta guía

        // El environment_id es el propio (pasa el primer chequeo), pero
        // el estados[] trae un child_id que no pertenece a este ambiente
        // — como si alguien hubiera editado el payload a mano.
        Livewire::actingAs($guia)
            ->test(RegistrarAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('fecha', '2026-09-21')
            ->set("estados.{$ninoAjeno->id}", Attendance::STATUS_PRESENTE)
            ->call('guardar');

        $this->assertDatabaseMissing('attendances', ['child_id' => $ninoAjeno->id]);
    }

    public function test_un_nino_inactivo_no_aparece_para_marcar(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente] = $this->ambienteConNinos(0);
        $ninoInactivo = Child::factory()->create(['environment_id' => $ambiente->id, 'status' => 'inactive']);

        $this->actingAs($guia);

        $page = new RegistrarAsistencia;
        $page->environment_id = $ambiente->id;

        $this->assertFalse($page->ninos()->contains('id', $ninoInactivo->id));
    }

    public function test_al_cambiar_de_fecha_precarga_los_estados_ya_registrados(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente, 'ninos' => $ninos] = $this->ambienteConNinos(1);

        Attendance::factory()->create([
            'child_id' => $ninos[0]->id,
            'date' => '2026-09-21',
            'status' => Attendance::STATUS_FALTA_JUSTIFICADA,
        ]);

        Livewire::actingAs($guia)
            ->test(RegistrarAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('fecha', '2026-09-21')
            ->assertSet("estados.{$ninos[0]->id}", Attendance::STATUS_FALTA_JUSTIFICADA);
    }
}

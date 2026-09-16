<?php

namespace Tests\Feature;

use App\Livewire\MiEscuelita\Asistencia;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\Content;
use App\Models\Environment;
use App\Models\Evidence;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La home de la familia y el calendario de asistencia muestran datos
 * reales (cuántas experiencias hay, cuántas esperan respuesta, el
 * estado de hoy) en vez de solo enlaces decorativos.
 */
class MiEscuelitaHomeTest extends TestCase
{
    use RefreshDatabase;

    private function familiaConHijo(): array
    {
        $guia = User::factory()->guia()->create();
        $ambiente = Environment::factory()->create(['teacher_id' => $guia->id]);
        $familia = Family::factory()->create();
        $nino = Child::factory()->create([
            'family_id' => $familia->id,
            'environment_id' => $ambiente->id,
        ]);
        $user = User::factory()->familia($familia)->create();

        return compact('guia', 'ambiente', 'familia', 'nino', 'user');
    }

    public function test_la_home_muestra_experiencias_disponibles_y_evidencias_sin_responder(): void
    {
        ['ambiente' => $ambiente, 'nino' => $nino, 'user' => $user, 'guia' => $guia] = $this->familiaConHijo();

        Content::factory()->published()->create(['environment_id' => $ambiente->id, 'teacher_id' => $guia->id]);
        $contenidoConEvidencia = Content::factory()->published()->requiresEvidence()->create([
            'environment_id' => $ambiente->id,
            'teacher_id' => $guia->id,
        ]);

        Evidence::factory()->create([
            'content_id' => $contenidoConEvidencia->id,
            'child_id' => $nino->id,
            'family_id' => $nino->family_id,
            'status' => Evidence::STATUS_SUBMITTED,
        ]);

        $this->actingAs($user)
            ->get(route('mi-escuelita.home'))
            ->assertOk()
            ->assertViewHas('totalExperiencias', 2)
            ->assertViewHas('sinResponder', 1);
    }

    public function test_la_home_no_cuenta_evidencias_ya_respondidas_como_pendientes(): void
    {
        ['ambiente' => $ambiente, 'nino' => $nino, 'user' => $user, 'guia' => $guia] = $this->familiaConHijo();

        $contenido = Content::factory()->published()->requiresEvidence()->create([
            'environment_id' => $ambiente->id,
            'teacher_id' => $guia->id,
        ]);

        Evidence::factory()->create([
            'content_id' => $contenido->id,
            'child_id' => $nino->id,
            'family_id' => $nino->family_id,
            'status' => Evidence::STATUS_RESPONDED,
        ]);

        $this->actingAs($user)
            ->get(route('mi-escuelita.home'))
            ->assertViewHas('sinResponder', 0);
    }

    public function test_la_home_muestra_la_asistencia_de_hoy(): void
    {
        ['nino' => $nino, 'user' => $user, 'guia' => $guia] = $this->familiaConHijo();

        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => now()->toDateString(),
            'status' => Attendance::STATUS_PRESENTE,
            'recorded_by' => $guia->id,
        ]);

        $this->actingAs($user)
            ->get(route('mi-escuelita.home'))
            ->assertViewHas('asistenciaHoy', Attendance::STATUS_PRESENTE);
    }

    public function test_el_calendario_alinea_los_dias_del_mes_con_su_estado(): void
    {
        ['nino' => $nino, 'user' => $user, 'guia' => $guia] = $this->familiaConHijo();
        $inicioDeMes = now()->startOfMonth();

        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => $inicioDeMes->copy()->day(1),
            'status' => Attendance::STATUS_PRESENTE,
            'recorded_by' => $guia->id,
        ]);

        $calendario = Livewire::actingAs($user)
            ->test(Asistencia::class)
            ->get('calendario');

        $celdas = collect($calendario)->flatten(1)->filter();

        $this->assertSame(
            $inicioDeMes->daysInMonth,
            $celdas->count(),
            'el calendario debe tener una celda por cada día del mes'
        );

        $primerDia = $celdas->firstWhere(fn ($celda) => $celda['fecha']->isSameDay($inicioDeMes));
        $this->assertSame(Attendance::STATUS_PRESENTE, $primerDia['status']);

        $diaSinRegistro = $celdas->firstWhere(fn ($celda) => $celda['fecha']->day === 2);
        $this->assertNull($diaSinRegistro['status']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Child;
use App\Models\Content;
use App\Models\Environment;
use App\Models\Evidence;
use App\Models\Family;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReiniciarDatosDemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_borra_familias_ninos_y_personal_de_prueba_pero_deja_el_admin_y_los_ambientes(): void
    {
        $admin = User::factory()->administrador()->create();
        $guia = User::factory()->guia()->create();
        $ambiente = Environment::factory()->create(['teacher_id' => $guia->id]);
        $familia = Family::factory()->create();
        $usuarioFamilia = User::factory()->familia($familia)->create();
        $nino = Child::factory()->create(['family_id' => $familia->id, 'environment_id' => $ambiente->id]);

        $contenido = Content::factory()->create(['teacher_id' => $guia->id]);
        Attendance::factory()->create(['child_id' => $nino->id]);
        Evidence::factory()->create(['content_id' => $contenido->id, 'child_id' => $nino->id, 'family_id' => $familia->id]);
        Observation::factory()->create(['child_id' => $nino->id, 'content_id' => $contenido->id, 'teacher_id' => $guia->id]);

        $this->artisan('aula:reiniciar-demo', ['--force' => true])
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseCount('users', 1);

        $this->assertDatabaseHas('environments', ['id' => $ambiente->id, 'teacher_id' => null]);

        $this->assertDatabaseCount('families', 0);
        $this->assertDatabaseCount('children', 0);
        $this->assertDatabaseCount('attendances', 0);
        $this->assertDatabaseCount('evidence', 0);
        $this->assertDatabaseCount('content', 0);
        $this->assertDatabaseCount('observations', 0);
    }

    public function test_conserva_las_areas_curriculares(): void
    {
        User::factory()->administrador()->create();
        \App\Models\Area::factory()->create(['name' => 'Lectura']);

        $this->artisan('aula:reiniciar-demo', ['--force' => true])->assertSuccessful();

        $this->assertDatabaseHas('areas', ['name' => 'Lectura']);
    }

    public function test_sin_force_pide_confirmacion_y_no_borra_si_se_rechaza(): void
    {
        User::factory()->administrador()->create();
        Family::factory()->create();

        $this->artisan('aula:reiniciar-demo')
            ->expectsConfirmation('¿Confirmás?', 'no')
            ->assertFailed();

        $this->assertDatabaseCount('families', 1);
    }
}

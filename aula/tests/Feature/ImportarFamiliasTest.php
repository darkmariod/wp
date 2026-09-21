<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Environment;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Carga masiva de familias reales para el despliegue: crear cada una a
 * mano desde el panel no escala cuando el colegio manda la lista
 * completa de un curso.
 */
class ImportarFamiliasTest extends TestCase
{
    use RefreshDatabase;

    private function archivoTemporal(string $contenido): string
    {
        $ruta = tempnam(sys_get_temp_dir(), 'familias_').'.txt';
        file_put_contents($ruta, $contenido);

        return $ruta;
    }

    public function test_crea_familia_usuario_y_ninos(): void
    {
        $ambiente = Environment::factory()->create(['name' => 'Inicial 1']);

        $archivo = $this->archivoTemporal(<<<'TXT'
            FAMILIA: Pérez García
            TELEFONO: 0991234567
            CORREO: juan.perez@example.test
            NINO: Juan Pérez | 2020-05-10 | Inicial 1
            NINO: Ana Pérez | 2022-03-15 | inicial 1
            TXT);

        $this->artisan('familias:importar', ['archivo' => $archivo])
            ->assertSuccessful();

        $family = Family::where('name', 'Pérez García')->first();
        $this->assertNotNull($family);
        $this->assertSame('0991234567', $family->phone);

        $this->assertSame(2, Child::where('family_id', $family->id)->count());
        $this->assertTrue(
            Child::where('family_id', $family->id)->where('environment_id', $ambiente->id)->exists()
        );

        $user = User::where('email', 'juan.perez@example.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isFamilia());
        $this->assertSame($family->id, $user->family_id);
        $this->assertTrue($user->active);

        unlink($archivo);
    }

    public function test_varias_familias_en_un_solo_archivo(): void
    {
        $archivo = $this->archivoTemporal(<<<'TXT'
            FAMILIA: Pérez García
            CORREO: juan.perez@example.test
            NINO: Juan Pérez | 2020-05-10

            FAMILIA: Torres López
            CORREO: maria.torres@example.test
            NINO: Pedro Torres | 2019-08-20
            TXT);

        $this->artisan('familias:importar', ['archivo' => $archivo])
            ->assertSuccessful();

        $this->assertSame(2, Family::count());
        $this->assertSame(2, User::where('role', User::ROLE_FAMILIA)->count());

        unlink($archivo);
    }

    public function test_saltea_un_correo_ya_existente_sin_tocar_el_resto(): void
    {
        User::factory()->create(['email' => 'juan.perez@example.test']);

        $archivo = $this->archivoTemporal(<<<'TXT'
            FAMILIA: Pérez García
            CORREO: juan.perez@example.test
            NINO: Juan Pérez | 2020-05-10

            FAMILIA: Torres López
            CORREO: maria.torres@example.test
            NINO: Pedro Torres | 2019-08-20
            TXT);

        $this->artisan('familias:importar', ['archivo' => $archivo])
            ->assertSuccessful();

        $this->assertFalse(Family::where('name', 'Pérez García')->exists());
        $this->assertTrue(Family::where('name', 'Torres López')->exists());

        unlink($archivo);
    }

    public function test_saltea_un_nino_con_ambiente_inexistente_pero_no_pierde_a_los_demas(): void
    {
        Environment::factory()->create(['name' => 'Inicial 1']);

        $archivo = $this->archivoTemporal(<<<'TXT'
            FAMILIA: Pérez García
            CORREO: juan.perez@example.test
            NINO: Juan Pérez | 2020-05-10 | Inicial 1
            NINO: Ana Pérez | 2022-03-15 | Ambiente Que No Existe
            TXT);

        $this->artisan('familias:importar', ['archivo' => $archivo])
            ->assertSuccessful();

        $family = Family::where('name', 'Pérez García')->first();
        $this->assertSame(1, Child::where('family_id', $family->id)->count());
        $this->assertSame('Juan Pérez', Child::where('family_id', $family->id)->first()->name);

        unlink($archivo);
    }

    public function test_saltea_una_familia_sin_ningun_nino_valido(): void
    {
        $archivo = $this->archivoTemporal(<<<'TXT'
            FAMILIA: Sin Niños
            CORREO: sinninos@example.test
            TXT);

        $this->artisan('familias:importar', ['archivo' => $archivo])
            ->assertFailed();

        $this->assertFalse(Family::where('name', 'Sin Niños')->exists());
        $this->assertFalse(User::where('email', 'sinninos@example.test')->exists());

        unlink($archivo);
    }

    public function test_falla_con_claridad_si_el_archivo_no_existe(): void
    {
        $this->artisan('familias:importar', ['archivo' => '/no/existe/nada.txt'])
            ->assertFailed();
    }
}

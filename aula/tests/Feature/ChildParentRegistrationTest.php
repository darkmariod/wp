<?php

namespace Tests\Feature;

use App\Filament\Resources\ChildResource\Pages\EditChild;
use App\Filament\Resources\ChildResource\RelationManagers\ParentsRelationManager;
use App\Models\Child;
use App\Models\Environment;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cubre el flujo pedido: desde la ficha de un niño, registrar el correo
 * del padre o la madre para que puedan entrar al portal de familias.
 * Los padres no cuelgan del niño sino de su Family (Child::parents() es un
 * hasManyThrough), así que el punto crítico a probar es que el usuario
 * creado quede en la Family correcta, no en el niño ni en otra familia.
 */
class ChildParentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function ninoConFamilia(): Child
    {
        $admin = User::factory()->administrador()->create();
        $ambiente = Environment::factory()->create();
        $familia = Family::factory()->create(['name' => 'Familia Torres']);

        return Child::factory()->create([
            'family_id' => $familia->id,
            'environment_id' => $ambiente->id,
            'name' => 'Lucas',
        ]);
    }

    public function test_el_admin_registra_un_padre_desde_la_ficha_del_nino(): void
    {
        $admin = User::factory()->administrador()->create();
        $nino = $this->ninoConFamilia();

        Livewire::actingAs($admin)
            ->test(ParentsRelationManager::class, [
                'ownerRecord' => $nino,
                'pageClass' => EditChild::class,
            ])
            ->callTableAction('create', data: [
                'name' => 'Marcela Torres',
                'email' => 'marcela.torres@example.com',
                'password' => 'password',
                'active' => true,
            ]);

        $padre = User::where('email', 'marcela.torres@example.com')->first();

        $this->assertNotNull($padre, 'Debería haberse creado el usuario del padre');
        $this->assertSame(User::ROLE_FAMILIA, $padre->role);
        $this->assertSame($nino->family_id, $padre->family_id);
        $this->assertTrue($padre->active);
    }

    public function test_la_tabla_de_padres_solo_muestra_los_de_la_family_del_nino(): void
    {
        $admin = User::factory()->administrador()->create();
        $nino = $this->ninoConFamilia();

        $suPadre = User::factory()->familia(Family::find($nino->family_id))->create([
            'name' => 'Padre correcto',
        ]);

        $otraFamilia = Family::factory()->create();
        $padreDeOtraFamilia = User::factory()->familia($otraFamilia)->create([
            'name' => 'Padre de otra familia',
        ]);

        Livewire::actingAs($admin)
            ->test(ParentsRelationManager::class, [
                'ownerRecord' => $nino,
                'pageClass' => EditChild::class,
            ])
            ->assertCanSeeTableRecords([$suPadre])
            ->assertCanNotSeeTableRecords([$padreDeOtraFamilia]);
    }

    public function test_no_permite_dos_padres_con_el_mismo_correo(): void
    {
        $admin = User::factory()->administrador()->create();
        $nino = $this->ninoConFamilia();

        User::factory()->create(['email' => 'repetido@example.com']);

        Livewire::actingAs($admin)
            ->test(ParentsRelationManager::class, [
                'ownerRecord' => $nino,
                'pageClass' => EditChild::class,
            ])
            ->callTableAction('create', data: [
                'name' => 'Otro padre',
                'email' => 'repetido@example.com',
                'password' => 'password',
                'active' => true,
            ])
            ->assertHasTableActionErrors(['email']);
    }

    public function test_el_padre_registrado_puede_iniciar_sesion_y_ve_a_su_hijo(): void
    {
        $admin = User::factory()->administrador()->create();
        $nino = $this->ninoConFamilia();

        Livewire::actingAs($admin)
            ->test(ParentsRelationManager::class, [
                'ownerRecord' => $nino,
                'pageClass' => EditChild::class,
            ])
            ->callTableAction('create', data: [
                'name' => 'Marcela Torres',
                'email' => 'marcela.torres@example.com',
                'password' => 'una-clave-larga',
                'active' => true,
            ]);

        $padre = User::where('email', 'marcela.torres@example.com')->first();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('una-clave-larga', $padre->password));

        $this->actingAs($padre)
            ->get(route('mi-escuelita.home'))
            ->assertOk()
            ->assertSee($nino->name);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use BezhanSalleh\FilamentShield\Resources\Roles\Pages\ListRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShieldAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_abrir_la_pagina_de_roles_de_shield(): void
    {
        Role::findOrCreate('super_admin', 'web');
        $admin = User::factory()->administrador()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin)
            ->get('/admin/shield/roles')
            ->assertOk();
    }

    public function test_la_pagina_de_roles_muestra_los_roles_existentes(): void
    {
        Role::findOrCreate('super_admin', 'web');
        Role::findOrCreate('administrador', 'web');
        Role::findOrCreate('guia', 'web');
        $admin = User::factory()->administrador()->create();
        $admin->assignRole('super_admin');

        Livewire::actingAs($admin)
            ->test(ListRoles::class)
            ->assertCanSeeTableRecords(Role::whereIn('name', ['administrador', 'guia'])->get());
    }

    public function test_familia_no_puede_abrir_la_pagina_de_roles(): void
    {
        $familia = User::factory()->familia()->create();

        $this->actingAs($familia)
            ->get('/admin/shield/roles')
            ->assertForbidden();
    }
}

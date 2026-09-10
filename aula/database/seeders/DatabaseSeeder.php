<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Environment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeder de ENTREGA: solo la estructura real y confirmada del
     * colegio (los 2 ambientes y las 6 áreas — esto no es data de
     * prueba, es la configuración real de Mentes Absorbentes / Mentes
     * Razonadoras) más la cuenta mínima para que el colegio pueda entrar
     * por primera vez y empezar a cargar todo lo demás desde el panel:
     * sus propias guías, familias, niños y contenido.
     *
     * Deliberadamente NO crea niños, familias ni contenido de ejemplo —
     * eso era fixture de desarrollo (30 niños Faker, contenido de
     * muestra) y no debe llegar a producción.
     */
    public function run(): void
    {
        $admin = $this->crearAdministrador();
        [$absorbentes, $razonadoras] = $this->crearAmbientes();
        $this->crearAreas();

        $this->command?->newLine();
        $this->command?->info('Listo. Iniciá sesión en /admin con la cuenta de arriba y cargá desde el panel: guías (Usuarios), familias y niños (Gestión de Familias) y el contenido (Gestión Académica).');
    }

    /**
     * Una sola cuenta de administrador para arrancar. La contraseña se
     * lee de ADMIN_PASSWORD si está en el .env; si no, se genera una
     * aleatoria y se imprime UNA vez en la consola — nunca queda una
     * contraseña previsible ("password") en el código fuente.
     */
    private function crearAdministrador(): User
    {
        $email = env('ADMIN_EMAIL', 'uepestalozzi.ambato@gmail.com');
        $password = env('ADMIN_PASSWORD');
        $generada = blank($password);

        if ($generada) {
            $password = Str::password(16);
        }

        $admin = User::factory()->administrador()->create([
            'name' => 'Administrador',
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // super_admin además de 'administrador': es lo que le da acceso a
        // la propia pantalla de Roles de Filament Shield (ver RolePolicy).
        $admin->assignRole(Role::findOrCreate('super_admin', 'web'));

        if ($generada) {
            $this->command?->warn("Administrador creado: {$email}");
            $this->command?->warn("Contraseña generada: {$password}");
            $this->command?->warn('Anotala ahora — no se vuelve a mostrar. Cambiala apenas inicies sesión.');
        }

        return $admin;
    }

    /**
     * Los 2 ambientes reales del colegio. Sin teacher_id: la guía real de
     * cada uno se asigna después, desde el panel, una vez que el
     * administrador cree esa cuenta con su correo real.
     *
     * @return array{0: Environment, 1: Environment}
     */
    private function crearAmbientes(): array
    {
        $absorbentes = Environment::factory()->create([
            'name' => 'Mentes Absorbentes',
            'age_range' => '3 a 6 años',
            'stage' => Environment::STAGE_ABSORBENTES,
            'order' => 1,
        ]);

        $razonadoras = Environment::factory()->razonadoras()->create([
            'name' => 'Mentes Razonadoras',
            'age_range' => '6 a 9 años',
            'stage' => Environment::STAGE_RAZONADORAS,
            'order' => 2,
        ]);

        return [$absorbentes, $razonadoras];
    }

    /**
     * Áreas obligatorias (Lectura, Matemática, Inglés) y abiertas
     * (Vida Práctica, Arte y Expresión, Cultura y Naturaleza).
     */
    private function crearAreas(): void
    {
        $obligatorias = [
            ['name' => 'Lectura', 'icon' => 'lectura'],
            ['name' => 'Matemática', 'icon' => 'matematica'],
            ['name' => 'Inglés', 'icon' => 'ingles'],
        ];

        $abiertas = [
            ['name' => 'Vida Práctica', 'icon' => 'vida-practica'],
            ['name' => 'Arte y Expresión', 'icon' => 'arte'],
            ['name' => 'Cultura y Naturaleza', 'icon' => 'cultura'],
        ];

        foreach ($obligatorias as $i => $datos) {
            Area::factory()->create([...$datos, 'order' => $i, 'is_required' => true]);
        }

        foreach ($abiertas as $i => $datos) {
            Area::factory()->create([...$datos, 'order' => count($obligatorias) + $i, 'is_required' => false]);
        }
    }
}

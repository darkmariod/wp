<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old CHECK constraint
        DB::statement("ALTER TABLE plan_cuentas DROP CONSTRAINT plan_cuentas_grupo_check");

        // Add new CHECK constraint with 'costo' included
        DB::statement("ALTER TABLE plan_cuentas ADD CONSTRAINT plan_cuentas_grupo_check CHECK (grupo::text = ANY (ARRAY['activo'::character varying, 'pasivo'::character varying, 'patrimonio'::character varying, 'ingreso'::character varying, 'gasto'::character varying, 'costo'::character varying]::text[]))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE plan_cuentas DROP CONSTRAINT plan_cuentas_grupo_check");

        DB::statement("ALTER TABLE plan_cuentas ADD CONSTRAINT plan_cuentas_grupo_check CHECK (grupo::text = ANY (ARRAY['activo'::character varying, 'pasivo'::character varying, 'patrimonio'::character varying, 'ingreso'::character varying, 'gasto'::character varying]::text[]))");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_cuentas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->enum('grupo', ['activo', 'pasivo', 'patrimonio', 'ingreso', 'gasto']);
            $table->enum('tipo', ['deudor', 'acreedor']);
            $table->boolean('es_auxiliar')->default(false);
            $table->foreignId('padre_id')->nullable()->constrained('plan_cuentas');
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_cuentas');
    }
};

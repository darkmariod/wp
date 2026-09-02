<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obras', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->foreignId('cliente_id')->constrained()->cascadeOnDelete();
            $table->string('direccion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin_estimada');
            $table->date('fecha_fin_real')->nullable();
            $table->enum('estado', ['planificada', 'en_curse', 'suspendida', 'culminada', 'cancelada'])->default('planificada');
            $table->decimal('contrato_monto', 14, 2)->default(0);
            $table->decimal('anticipo_porcentaje', 5, 2)->default(0);
            $table->decimal('aiu_administracion', 5, 2)->default(10);
            $table->decimal('aiu_imprevistos', 5, 2)->default(5);
            $table->decimal('aiu_utilidad', 5, 2)->default(10);
            $table->decimal('costo_fijo_mensual', 14, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('cliente_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obras');
    }
};

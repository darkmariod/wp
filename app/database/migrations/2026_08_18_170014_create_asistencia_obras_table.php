<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_obras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trabajador_id')->constrained('trabajadores')->cascadeOnDelete();
            $table->date('fecha');
            $table->decimal('horas_trabajadas', 5, 2)->default(0);
            $table->time('hora_entrada')->nullable();
            $table->time('hora_salida')->nullable();
            $table->enum('tipo_jornada', ['normal', 'extraordinaria', 'dominical_feriado'])->default('normal');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('obra_id');
            $table->index('trabajador_id');
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia_obras');
    }
};

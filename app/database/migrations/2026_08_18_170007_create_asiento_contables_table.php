<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asiento_contables', function (Blueprint $table) {
            $table->id();
            $table->string('numero_asiento')->unique();
            $table->date('fecha');
            $table->text('descripcion');
            $table->foreignId('obra_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('tipo', ['manual', 'automatico', 'cierre', 'apertura']);
            $table->enum('estado', ['borrador', 'aprobado', 'anulado'])->default('borrador');
            $table->foreignId('usuario_creacion')->constrained('users');
            $table->foreignId('usuario_aprobacion')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('obra_id');
            $table->index('fecha');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asiento_contables');
    }
};

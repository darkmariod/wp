<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_apus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presupuesto_id')->constrained('presupuestos')->cascadeOnDelete();
            $table->enum('tipo', ['material', 'mano_obra', 'subcontrato', 'equipo']);
            $table->string('descripcion');
            $table->string('unidad_medida');
            $table->decimal('cantidad', 14, 4)->default(0);
            $table->decimal('costo_unitario', 14, 6)->default(0);
            $table->decimal('costo_total', 14, 2)->default(0);
            $table->timestamps();

            $table->index('presupuesto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_apus');
    }
};

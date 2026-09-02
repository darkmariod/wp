<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained()->cascadeOnDelete();
            $table->string('codigo')->unique();
            $table->string('descripcion');
            $table->string('unidad_medida');
            $table->decimal('cantidad', 14, 4)->default(0);
            $table->decimal('costo_unitario', 14, 6)->default(0);
            $table->decimal('precio_venta_unitario', 14, 6)->default(0);
            $table->decimal('subtotal_costo', 14, 2)->default(0);
            $table->decimal('subtotal_venta', 14, 2)->default(0);
            $table->timestamps();

            $table->index('obra_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuestos');
    }
};

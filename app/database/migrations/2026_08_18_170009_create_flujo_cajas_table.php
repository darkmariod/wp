<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flujo_cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained()->cascadeOnDelete();
            $table->date('fecha');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->enum('categoria', [
                'anticipo_cliente',
                'pago_cliente',
                'compra_material',
                'pago_mano_obra',
                'pago_subcontrato',
                'pago_equipo',
                'gasto_administrativo',
                'otro',
            ]);
            $table->decimal('monto', 14, 2)->default(0);
            $table->string('referencia')->nullable();
            $table->foreignId('asiento_id')->nullable()->constrained('asiento_contables')->nullOnDelete();
            $table->timestamps();

            $table->index('obra_id');
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flujo_cajas');
    }
};

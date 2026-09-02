<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amortizacion_anticipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anticipo_id')->constrained('anticipo_clientes')->cascadeOnDelete();
            $table->integer('numero_amortizacion');
            $table->decimal('porcentaje_amortizar', 5, 2)->default(0);
            $table->decimal('monto_amortizado', 14, 2)->default(0);
            $table->decimal('avance_porcentaje', 5, 2)->default(0);
            $table->date('fecha_amortizacion');
            $table->foreignId('asiento_id')->nullable()->constrained('asiento_contables')->nullOnDelete();
            $table->timestamps();

            $table->index('anticipo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amortizacion_anticipos');
    }
};

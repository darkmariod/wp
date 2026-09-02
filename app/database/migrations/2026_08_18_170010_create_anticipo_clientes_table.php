<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anticipo_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained()->cascadeOnDelete();
            $table->decimal('monto_total', 14, 2)->default(0);
            $table->decimal('porcentaje', 5, 2)->default(0);
            $table->enum('estado', ['pendiente', 'parcial', 'amortizado', 'cancelado'])->default('pendiente');
            $table->date('fecha_concesion');
            $table->timestamps();

            $table->index('obra_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anticipo_clientes');
    }
};

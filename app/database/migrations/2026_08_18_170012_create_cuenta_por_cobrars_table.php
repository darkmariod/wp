<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuenta_por_cobrars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained()->cascadeOnDelete();
            $table->enum('tipo', ['factura', 'nota_venta', 'anticipos']);
            $table->string('numero_comprobante');
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->decimal('monto_total', 14, 2)->default(0);
            $table->decimal('monto_cobrado', 14, 2)->default(0);
            $table->enum('estado', ['pendiente', 'parcial', 'cobrada', 'vencida', 'mora'])->default('pendiente');
            $table->timestamps();

            $table->index('obra_id');
            $table->index('cliente_id');
            $table->index('fecha_emision');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuenta_por_cobrars');
    }
};

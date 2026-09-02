<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_asientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asiento_id')->constrained('asiento_contables')->cascadeOnDelete();
            $table->foreignId('cuenta_id')->constrained('plan_cuentas');
            $table->decimal('debe', 14, 2)->default(0);
            $table->decimal('haber', 14, 2)->default(0);
            $table->string('referencia')->nullable();
            $table->timestamps();

            $table->index('asiento_id');
            $table->index('cuenta_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_asientos');
    }
};

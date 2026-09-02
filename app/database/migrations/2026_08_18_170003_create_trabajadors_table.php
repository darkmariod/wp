<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trabajadores', function (Blueprint $table) {
            $table->id();
            $table->char('cedula', 10)->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('cargo')->nullable();
            $table->decimal('sueldo_base', 10, 2)->default(0);
            $table->enum('tipo_contrato', ['indefinido', 'obra_determinada', 'servicio']);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trabajadores');
    }
};

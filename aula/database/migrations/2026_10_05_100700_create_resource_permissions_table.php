<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Una fila vale para un rol o para un usuario, no para ambos: lo valida
// la aplicación.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->string('role', 30)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->boolean('can_view')->default(true);
            $table->boolean('can_download')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_permissions');
    }
};

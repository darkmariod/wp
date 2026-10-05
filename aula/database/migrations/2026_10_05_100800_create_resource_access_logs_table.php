<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // viewed|downloaded
            $table->string('action', 20);
            // Solo created_at: un registro de acceso nunca se edita.
            $table->timestamp('created_at')->useCurrent();

            $table->index(['resource_id', 'action']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_access_logs');
    }
};

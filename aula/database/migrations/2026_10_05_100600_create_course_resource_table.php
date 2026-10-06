<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Que el módulo pertenezca al mismo curso lo valida la aplicación, no la base.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_resource', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->constrained('modules')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['course_id', 'resource_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_resource');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            // experience|reading|video|task|document|gallery|announcement
            $table->string('type');
            $table->text('description')->nullable();
            $table->longText('body')->nullable();
            $table->foreignId('environment_id')->nullable()->constrained('environments')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('cover_image')->nullable();
            // Los archivos (PDF, galería) van por la relación morphMany
            // media() de abajo, servidos por MediaController (Fase 10) —
            // nunca un path suelto. video_url sí es un campo simple: es
            // un link externo (YouTube/Drive), no un archivo que proteger.
            $table->string('video_url')->nullable();
            $table->boolean('requires_evidence')->default(false);
            $table->timestamp('published_at')->nullable();
            // draft|scheduled|published|archived
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content');
    }
};

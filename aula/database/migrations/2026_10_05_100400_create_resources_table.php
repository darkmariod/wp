<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Sin softDeletes: borrar un recurso es un borrado real (los archivos
// físicos se limpian aparte, no con la base de datos).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // pdf|document|presentation|video|audio|image|link|file (enum ResourceType)
            $table->string('type', 20);
            $table->foreignId('category_id')->nullable()->constrained('resource_categories')->nullOnDelete();
            // Ruta dentro del disco privado `biblioteca`, nunca una URL pública.
            $table->string('thumbnail')->nullable();
            // Enlaces y videos por URL; los archivos van en resource_files.
            $table->string('external_url', 2048)->nullable();
            // draft|published|archived (enum ResourceStatus)
            $table->string('status', 20)->default('draft');
            $table->boolean('is_downloadable')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('type');
            $table->index('category_id');
            $table->index('created_by');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};

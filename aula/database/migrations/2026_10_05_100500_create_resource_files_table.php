<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->string('disk', 40);
            $table->string('path', 1024);
            $table->string('original_name');
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('size');
            $table->string('extension', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_files');
    }
};

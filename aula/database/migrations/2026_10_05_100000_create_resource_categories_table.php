<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            // Las categorías marcadas aparecen como pestañas en la Biblioteca.
            $table->boolean('show_in_tabs')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_categories');
    }
};

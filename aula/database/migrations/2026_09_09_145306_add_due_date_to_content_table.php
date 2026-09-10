<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content', function (Blueprint $table) {
            // Fecha límite para entregar/compartir esta actividad. Distinta
            // de published_at (cuándo se hace visible): due_date es cuándo
            // se espera que la familia ya haya respondido. Nula por defecto:
            // no todo contenido tiene vencimiento (un anuncio no lo necesita).
            $table->date('due_date')->nullable()->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('content', function (Blueprint $table) {
            $table->dropColumn('due_date');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // administrador|coordinacion|guia|familia
            $table->string('role')->default('familia')->after('email');
            $table->foreignId('family_id')->nullable()->after('role')
                ->constrained('families')->nullOnDelete();
            $table->boolean('active')->default(true)->after('family_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('family_id');
            $table->dropColumn(['role', 'active']);
        });
    }
};

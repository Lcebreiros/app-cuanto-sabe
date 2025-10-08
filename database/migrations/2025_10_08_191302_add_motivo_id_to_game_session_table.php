<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            // Crear la columna solo si no existe
            if (!Schema::hasColumn('game_sessions', 'motivo_id')) {
                $table->unsignedBigInteger('motivo_id')->after('id'); // o colocar 'after' donde quieras
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            // Borrar la columna si existe
            if (Schema::hasColumn('game_sessions', 'motivo_id')) {
                $table->dropColumn('motivo_id');
            }
        });
    }
};

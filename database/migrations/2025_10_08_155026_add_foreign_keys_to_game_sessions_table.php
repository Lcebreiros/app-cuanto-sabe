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
            // Asegurarse de que la columna exista
            if (!Schema::hasColumn('game_sessions', 'motivo_id')) {
                $table->unsignedBigInteger('motivo_id');
            }

            // Crear la FK
            $table->foreign('motivo_id')
                ->references('id')
                ->on('motivos')
                ->onUpdate('restrict')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropForeign(['motivo_id']);
        });
    }
};

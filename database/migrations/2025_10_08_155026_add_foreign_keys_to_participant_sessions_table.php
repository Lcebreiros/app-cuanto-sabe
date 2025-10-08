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
        Schema::table('participant_sessions', function (Blueprint $table) {
            $table->foreign(['game_session_id'])->references(['id'])->on('game_sessions')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participant_sessions', function (Blueprint $table) {
            $table->dropForeign('participant_sessions_game_session_id_foreign');
        });
    }
};

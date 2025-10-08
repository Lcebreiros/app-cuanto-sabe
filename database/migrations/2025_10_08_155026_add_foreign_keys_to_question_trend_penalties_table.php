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
        Schema::table('question_trend_penalties', function (Blueprint $table) {
            $table->foreign(['game_session_id'])->references(['id'])->on('game_sessions')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['question_id'])->references(['id'])->on('questions')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_trend_penalties', function (Blueprint $table) {
            $table->dropForeign('question_trend_penalties_game_session_id_foreign');
            $table->dropForeign('question_trend_penalties_question_id_foreign');
        });
    }
};

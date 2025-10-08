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
        Schema::table('guest_answers', function (Blueprint $table) {
            $table->foreign(['game_session_id'])->references(['id'])->on('game_sessions')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['question_id'])->references(['id'])->on('questions')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_answers', function (Blueprint $table) {
            $table->dropForeign('guest_answers_game_session_id_foreign');
            $table->dropForeign('guest_answers_question_id_foreign');
        });
    }
};

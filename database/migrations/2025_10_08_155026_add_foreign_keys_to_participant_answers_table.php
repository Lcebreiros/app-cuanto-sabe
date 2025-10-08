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
        Schema::table('participant_answers', function (Blueprint $table) {
            $table->foreign(['participant_session_id'])->references(['id'])->on('participant_sessions')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['question_id'])->references(['id'])->on('questions')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participant_answers', function (Blueprint $table) {
            $table->dropForeign('participant_answers_participant_session_id_foreign');
            $table->dropForeign('participant_answers_question_id_foreign');
        });
    }
};

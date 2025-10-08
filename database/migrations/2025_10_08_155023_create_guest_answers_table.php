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
        if (!Schema::hasTable('guest_answers')) {
        Schema::create('guest_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('game_session_id')->index('guest_answers_game_session_id_foreign');
            $table->unsignedBigInteger('question_id')->index('guest_answers_question_id_foreign');
            $table->boolean('is_correct')->default(false);
            $table->integer('points_awarded')->default(0);
            $table->boolean('apuesta_x2')->default(false);
            $table->boolean('was_discarded')->default(false);
            $table->timestamps();
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_answers');
    }
};

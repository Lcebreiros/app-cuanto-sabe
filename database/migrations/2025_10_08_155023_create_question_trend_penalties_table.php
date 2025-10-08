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
        if (!Schema::hasTable('question_trend_penalties')) {
        Schema::create('question_trend_penalties', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('game_session_id');
            $table->unsignedBigInteger('question_id')->index('question_trend_penalties_question_id_foreign');
            $table->integer('penalty_count')->default(0);
            $table->timestamps();

            $table->unique(['game_session_id', 'question_id']);
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_trend_penalties');
    }
};

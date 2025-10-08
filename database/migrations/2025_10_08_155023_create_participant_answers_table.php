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
        if (!Schema::hasTable('participant_answers')) {
        Schema::create('participant_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('participant_session_id');
            $table->unsignedBigInteger('question_id')->index('participant_answers_question_id_foreign');
            $table->string('option_label', 2);
            $table->timestamps();
            $table->string('label_correcto', 1)->nullable();

            $table->unique(['participant_session_id', 'question_id']);
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_answers');
    }
};

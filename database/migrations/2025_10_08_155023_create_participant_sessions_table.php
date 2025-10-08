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
        if (!Schema::hasTable('participant_sessions')) {
        Schema::create('participant_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('game_session_id')->index('participant_sessions_game_session_id_foreign');
            $table->string('username');
            $table->string('dni_last4', 4);
            $table->enum('status', ['pending', 'waiting', 'active', 'playing'])->default('pending');
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->integer('puntaje')->default(0);
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_sessions');
    }
};

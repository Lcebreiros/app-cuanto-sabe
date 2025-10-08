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
        if (!Schema::hasTable('game_sessions')) {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('guest_name');
            $table->unsignedBigInteger('motivo_id')->index('game_sessions_motivo_id_foreign');
            $table->unsignedBigInteger('active_question_id')->nullable();
            $table->enum('status', ['active', 'ended'])->default('active');
            $table->timestamps();
            $table->json('pregunta_json')->nullable();
            $table->integer('guest_points')->default(0);
            $table->boolean('apuesta_x2_active')->default(false);
            $table->unsignedTinyInteger('descarte_usados')->default(0);
            $table->enum('modo_juego', ['normal', 'express'])->default('normal');
            $table->unsignedTinyInteger('apuesta_x2_usadas')->default(0);
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};

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
        if (!Schema::hasTable('questions')) {
        Schema::create('questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id')->nullable()->index('questions_category_id_foreign');
            $table->string('texto');
            $table->integer('correct_index');
            $table->timestamps();
            $table->string('opcion_correcta')->nullable();
            $table->string('opcion_1')->nullable();
            $table->string('opcion_2')->nullable();
            $table->string('opcion_3')->nullable();
            $table->boolean('is_active')->default(false);
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};

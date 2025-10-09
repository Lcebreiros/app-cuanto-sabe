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
        Schema::table('users', function (Blueprint $table) {
            // Agregar el campo dni_ultimo4 si no existe
            if (!Schema::hasColumn('users', 'dni_ultimo4')) {
                $table->string('dni_ultimo4')->after('name');
            }
            
            // Agregar el campo role si no existe
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('dni_ultimo4');
            }
            
            // Hacer el email nullable si vas a usar solo dni_ultimo4
            $table->string('email')->nullable()->change();
            
            // Hacer el password nullable si vas a usar solo dni_ultimo4
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['dni_ultimo4', 'role']);
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
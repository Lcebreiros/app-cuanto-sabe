<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // AGREGAR id como BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY si no existe
        if (! Schema::hasColumn('game_sessions', 'id')) {
            // Nota: esto fallará si ya existe otra llave primaria en la tabla.
            DB::statement("
                ALTER TABLE `game_sessions`
                ADD COLUMN `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST
            ");
        }

        Schema::table('game_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('game_sessions', 'guest_name')) {
                $table->string('guest_name', 255)->nullable(false);
            }

            if (! Schema::hasColumn('game_sessions', 'motivo_id')) {
                // se solicitó NOT NULL y MUL (index)
                $table->unsignedBigInteger('motivo_id')->nullable(false);
                $table->index('motivo_id');
            }

            if (! Schema::hasColumn('game_sessions', 'active_question_id')) {
                $table->unsignedBigInteger('active_question_id')->nullable();
            }

            if (! Schema::hasColumn('game_sessions', 'status')) {
                $table->enum('status', ['active', 'ended'])->default('active');
            }

            if (! Schema::hasColumn('game_sessions', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('game_sessions', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }

            if (! Schema::hasColumn('game_sessions', 'pregunta_json')) {
                $table->longText('pregunta_json')->nullable();
            }

            if (! Schema::hasColumn('game_sessions', 'guest_points')) {
                $table->integer('guest_points')->default(0);
            }

            if (! Schema::hasColumn('game_sessions', 'apuesta_x2_active')) {
                // tinyint(1)
                $table->boolean('apuesta_x2_active')->default(false);
            }

            if (! Schema::hasColumn('game_sessions', 'descarte_usados')) {
                // tinyint(3) unsigned
                $table->unsignedTinyInteger('descarte_usados')->default(0);
            }

            if (! Schema::hasColumn('game_sessions', 'modo_juego')) {
                $table->enum('modo_juego', ['normal', 'express'])->default('normal');
            }

            if (! Schema::hasColumn('game_sessions', 'apuesta_x2_usadas')) {
                $table->unsignedTinyInteger('apuesta_x2_usadas')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            // eliminamos columnas si existen (cuidado con id si ya existía y la tabla lo necesita)
            if (Schema::hasColumn('game_sessions', 'apuesta_x2_usadas')) {
                $table->dropColumn('apuesta_x2_usadas');
            }
            if (Schema::hasColumn('game_sessions', 'modo_juego')) {
                $table->dropColumn('modo_juego');
            }
            if (Schema::hasColumn('game_sessions', 'descarte_usados')) {
                $table->dropColumn('descarte_usados');
            }
            if (Schema::hasColumn('game_sessions', 'apuesta_x2_active')) {
                $table->dropColumn('apuesta_x2_active');
            }
            if (Schema::hasColumn('game_sessions', 'guest_points')) {
                $table->dropColumn('guest_points');
            }
            if (Schema::hasColumn('game_sessions', 'pregunta_json')) {
                $table->dropColumn('pregunta_json');
            }
            if (Schema::hasColumn('game_sessions', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
            if (Schema::hasColumn('game_sessions', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('game_sessions', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('game_sessions', 'active_question_id')) {
                $table->dropColumn('active_question_id');
            }
            if (Schema::hasColumn('game_sessions', 'motivo_id')) {
                // drop index si existe antes de dropear la columna
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = array_map(fn($i) => $i->getName(), $sm->listTableIndexes('game_sessions'));
                if (in_array('motivo_id', $indexes, true)) {
                    // intentar drop index (nombre por convención puede variar)
                    try {
                        $table->dropIndex(['motivo_id']);
                    } catch (\Throwable $e) {
                        // ignorar si no se puede
                    }
                }
                $table->dropColumn('motivo_id');
            }
            if (Schema::hasColumn('game_sessions', 'guest_name')) {
                $table->dropColumn('guest_name');
            }

            // NOTA: NO se borra 'id' automáticamente en down() porque puede ser clave primaria histórica.
            // Si realmente querés eliminarla en down(), descomenta la sección y úsala con cautela.
        });

        // Si querés forzar la eliminación de id en down, podrías ejecutar:
        // if (Schema::hasColumn('game_sessions', 'id')) {
        //     DB::statement("ALTER TABLE `game_sessions` DROP COLUMN `id`");
        // }
    }
};

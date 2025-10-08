<?php

// app/Models/GameSession.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GameSession extends Model
{
    // columnas que se pueden setear en mass-assignment
    protected $fillable = [
        'guest_name',
        'motivo_id',
        'status',
        'guest_points',
        'modo_juego',
        'active_question_id',
        'pregunta_json',
        // columnas de bonus ya existentes
        'apuesta_x2_active',
        'apuesta_x2_usadas',
        'descarte_usados',
    ];

    // valores por defecto al crear una nueva sesión
    protected $attributes = [
        'modo_juego' => 'normal',
        'guest_points' => 0,
        'status' => 'active',
        'apuesta_x2_active' => false,
        'apuesta_x2_usadas' => 0,
        'descarte_usados' => 0,
    ];

    // castear tipos para facilitar uso en PHP
    protected $casts = [
        'apuesta_x2_active' => 'boolean',
        'apuesta_x2_usadas'  => 'integer',
        'descarte_usados'    => 'integer',
        'guest_points'       => 'integer',
        'pregunta_json'      => 'array',
    ];

    /* -------------------------
       Relaciones
       ------------------------- */
    public function motivo()
    {
        return $this->belongsTo(Motivo::class);
    }

    public function participants()
    {
        return $this->hasMany(\App\Models\ParticipantSession::class);
    }

    /* -------------------------
       Helpers de modo
       ------------------------- */
    public function isExpress(): bool
    {
        return $this->modo_juego === 'express';
    }

    public function isNormal(): bool
    {
        return $this->modo_juego === 'normal';
    }

    /**
     * Límite máximo de apuestas por sesión según modo.
     */
    public function apuestaLimite(): int
    {
        return $this->isExpress() ? 1 : 2;
    }

    /* -------------------------
       Consultas rápidas
       ------------------------- */
    public function apuestaDisponible(): bool
    {
        return $this->apuesta_x2_usadas < $this->apuestaLimite();
    }

    public function descarteDisponible(): bool
    {
        // si el descarte permitido es 1 por sesión
        return $this->descarte_usados < 1;
    }

    public function apuestaDisponiblesCount(): int
    {
        return max(0, $this->apuestaLimite() - (int)$this->apuesta_x2_usadas);
    }

    /* -------------------------
       Métodos que consumen bonos de forma segura (transaction + lock)
       Devuelven true si se consumió, false si no (p. ej. por límite)
       ------------------------- */

    /**
     * Intentar consumir la apuesta x2 para esta sesión.
     * Aplica la lógica de límite y desactiva apuesta_x2_active.
     * @return bool true si la apuesta fue aplicada/consumida, false si no había disponibles
     */
/**
 * Intentar consumir la apuesta x2 para esta sesión.
 */
public function consumirApuesta(): bool
{
    return DB::transaction(function () {
        $s = self::where('id', $this->id)->lockForUpdate()->first();

        $limite = $s->apuestaLimite();

        if (!$s->apuesta_x2_active) {
            return false;
        }

        if ($s->apuesta_x2_usadas >= $limite) {
            $s->apuesta_x2_active = false;
            $s->save();
            return false;
        }

        // Consumir la apuesta
        $s->apuesta_x2_usadas += 1;
        $s->apuesta_x2_active = false;
        $s->save();

        // ✅ Broadcast aquí directamente
        broadcast(new \App\Events\GameBonusUpdated($s));

        return true;
    });
}

    /**
     * Intentar usar un descarte para esta sesión.
     * @return bool true si se aplicó el descarte, false si ya se había usado
     */
    public function consumirDescarte(): bool
    {
        return DB::transaction(function () {
            $s = self::where('id', $this->id)->lockForUpdate()->first();

            if ($s->descarte_usados >= 1) {
                return false;
            }

            $s->descarte_usados += 1;
            $s->save();

            // broadcast(new \App\Events\GameBonusUpdated($s));

            return true;
        });
    }
}

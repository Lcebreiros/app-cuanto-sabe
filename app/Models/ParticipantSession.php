<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipantSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_session_id',
        'username',
        'dni_last4',
        'order',
        'status',
        'puntaje',
    ];

    // Relación con la sesión de juego
    public function gameSession()
    {
        return $this->belongsTo(GameSession::class);
    }
}


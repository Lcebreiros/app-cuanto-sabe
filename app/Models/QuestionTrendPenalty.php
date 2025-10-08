<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionTrendPenalty extends Model
{
    use HasFactory;

    protected $table = 'question_trend_penalties';

    protected $fillable = [
        'game_session_id',
        'question_id',
        'penalty_count',
    ];

    /**
     * Relación: pertenece a una sesión de juego
     */
    public function gameSession()
    {
        return $this->belongsTo(GameSession::class);
    }

    /**
     * Relación: pertenece a una pregunta
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

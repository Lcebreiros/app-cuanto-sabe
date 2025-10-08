<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipantAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_session_id',
        'question_id',
        'option_label',
        'label_correcto',
    ];

    // Relación con la sesión de participante
    public function participantSession()
    {
        return $this->belongsTo(ParticipantSession::class);
    }

    // Relación con la pregunta
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

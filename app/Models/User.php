<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'dni_ultimo4',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'dni_ultimo4', // ocultamos para que no se vea el hash
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Override the method Laravel uses to get the password for authentication.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->dni_ultimo4;
    }

    // En app/Models/User.php

public function participantSessions()
{
    return $this->hasMany(ParticipantSession::class);
}

// Para obtener la sesión activa (puedes ajustar la lógica del "status")
public function participantSessionActual()
{
    return $this->participantSessions()->where('status', 'activo')->latest()->first();
}

}

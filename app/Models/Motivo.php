<?php

// app/Models/Motivo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Motivo extends Model
{
    protected $fillable = ['nombre'];

    public function categorias()
    {
        return $this->hasMany(Categoria::class, 'motivo_id', 'id');
    }
}

<?php

// app/Models/Categoria.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['nombre', 'motivo_id'];

    public function motivo()
    {
        return $this->belongsTo(Motivo::class);
    }
}

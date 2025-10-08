<?php

namespace App\Http\Controllers;

use App\Models\Motivo;
use App\Models\Categoria;

class QuestionController extends Controller
{
    public function index()
    {
        $motivos     = Motivo::orderBy('nombre')->get();
        $categorias  = Categoria::with('motivo')->orderBy('nombre')->get();

        return view('questions', compact('motivos', 'categorias'));
    }
}

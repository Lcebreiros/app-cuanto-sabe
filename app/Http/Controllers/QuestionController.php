<?php

namespace App\Http\Controllers;

use App\Models\Motivo;
use App\Models\Categoria;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        $motivos     = Motivo::orderBy('nombre')->get();
        $categorias  = Categoria::with('motivo')->orderBy('nombre')->get();
        $questions   = Question::with(['categoria.motivo'])->orderByDesc('id')->get();

        return view('questions', compact('motivos', 'categorias', 'questions'));
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:questions,id',
        ]);

        $deleted = Question::whereIn('id', $data['ids'])->delete();

        return back()->with('status', "Preguntas eliminadas: {$deleted}");
    }
}

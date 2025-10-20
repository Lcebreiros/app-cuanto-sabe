<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Categoria;
use App\Models\Motivo;
use App\Models\Question;

class GameController extends Controller
{
    public function controlPanel()
    {
        // Cachear por 1 hora (rara vez cambian)
        $motivos = Cache::remember('motivos_with_categorias', 3600, function() {
            return Motivo::with('categorias')->get();
        });

        $categorias = Cache::remember('all_categorias', 3600, function() {
            return Categoria::all();
        });

        return view('game', compact('motivos', 'categorias'));
    }

    public function storeMotivo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:motivos,nombre',
        ]);
        
        Motivo::create(['nombre' => $request->nombre]);
        
        // Invalidar caché
        Cache::forget('motivos_with_categorias');
        Cache::forget('all_motivos');
        
        return back()->with('success', 'Motivo creado correctamente.');
    }

    public function storeCategoria(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'motivo_id' => 'required|exists:motivos,id',
        ]);
        
        Categoria::create([
            'nombre' => $request->nombre,
            'motivo_id' => $request->motivo_id
        ]);
        
        // Invalidar caché
        Cache::forget('all_categorias');
        Cache::forget('motivos_with_categorias');
        
        return back()->with('success', 'Categoría creada correctamente.');
    }

    public function bulkDeleteMotivos(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:motivos,id',
        ]);

        $ids = $data['ids'];

        // Eliminar motivos seleccionados (FKs en categorias y game_sessions están en cascade)
        $deleted = Motivo::whereIn('id', $ids)->delete();

        // Limpiar cachés relacionados
        Cache::forget('motivos_with_categorias');
        Cache::forget('all_motivos');
        Cache::forget('all_categorias');

        return back()->with('status', "Motivos eliminados: {$deleted}");
    }

    public function bulkDeleteCategorias(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:categorias,id',
        ]);

        $ids = $data['ids'];

        // Eliminar categorías seleccionadas (questions.category_id está en SET NULL)
        $deleted = Categoria::whereIn('id', $ids)->delete();

        // Limpiar cachés relacionados
        Cache::forget('motivos_with_categorias');
        Cache::forget('all_categorias');

        return back()->with('status', "Categorías eliminadas: {$deleted}");
    }

    public function storePregunta(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categorias,id',
            'texto' => 'required|string|max:255',
            'opcion_correcta' => 'required|string|max:255',
            'opcion_1' => 'required|string|max:255',
            'opcion_2' => 'required|string|max:255',
            'opcion_3' => 'required|string|max:255',
        ]);
        
        Question::create([
            'category_id' => $request->category_id,
            'texto' => $request->texto,
            'opcion_correcta' => $request->opcion_correcta,
            'opcion_1' => $request->opcion_1,
            'opcion_2' => $request->opcion_2,
            'opcion_3' => $request->opcion_3,
            'correct_index' => 0,
        ]);
        
        return back()->with('success', 'Pregunta creada correctamente.');
    }
}

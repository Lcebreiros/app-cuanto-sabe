<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'texto',
        'category_id',    // Relación a categoría
        'opcion_correcta',
        'opcion_1',
        'opcion_2',
        'opcion_3',
        'correct_index',
        'is_active',      // Marca si la pregunta está activa en el overlay
    ];

    // Relación: pregunta pertenece a una categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'category_id');
    }

    // Relación indirecta: pregunta pertenece a un motivo a través de categoría
    public function motivo()
    {
        // Permite acceder a $question->motivo directamente
        return $this->categoria ? $this->categoria->motivo : null;
    }

    // Marcar esta pregunta como activa (puedes hacer que sólo haya una activa a la vez)
    public function activar()
    {
        // Desactivar otras preguntas si quieres sólo una activa
        self::where('is_active', true)->update(['is_active' => false]);
        $this->is_active = true;
        $this->save();
    }

    // Desactivar la pregunta
    public function desactivar()
    {
        $this->is_active = false;
        $this->save();
    }

    public function getCorrectLabel()
{
    // Busca cuál label corresponde a la opción correcta (A, B, C o D)
    $opciones = [
        'A' => $this->opcion_correcta,
        'B' => $this->opcion_1,
        'C' => $this->opcion_2,
        'D' => $this->opcion_3,
    ];
    foreach ($opciones as $label => $texto) {
        if (trim($texto) === trim($this->opcion_correcta)) {
            return $label;
        }
    }
    return null; // Si no la encuentra
}

}

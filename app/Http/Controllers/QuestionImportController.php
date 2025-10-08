<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestionImportController extends Controller
{
    public function create()
    {
        return view('questions.import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv' => ['required','file','mimes:csv,txt','max:10240'], // 10MB
            'crear_categorias' => ['nullable','boolean'],
            'modo' => ['required','in:insert,upsert'], // insert: solo nuevas | upsert: crea o actualiza por texto+categoria
        ]);

        $crearCategorias = (bool)$request->boolean('crear_categorias');
        $modo = $request->input('modo', 'insert');

        // Abrir archivo
        $path = $request->file('csv')->getRealPath();
        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->withErrors(['csv' => 'No se pudo abrir el archivo.']);
        }

        // Detectar delimitador simple
        $firstLine = fgets($handle);
        $delimiter = $this->guessDelimiter($firstLine);
        // Volver al inicio para leer completo
        rewind($handle);

        // Leer cabeceras
        $headers = fgetcsv($handle, 0, $delimiter);
        if (!$headers) {
            return back()->withErrors(['csv' => 'El CSV está vacío o no tiene cabecera.']);
        }

        $headers = array_map(fn($h) => $this->normalizeHeader($h), $headers);

        // Mapeo esperado (flexible)
        // Podés agregar alias según tus planillas
        $map = [
            'category'  => ['categoria','category','cat','categoria_nombre','category_name'],
            'texto'     => ['pregunta','texto','enunciado','question'],
            'a'         => ['a','opcion_a','opcion1','opcion_1','opcion-1','b? no'], // 'b? no' es un distractor para que no choque
            'b'         => ['b','opcion_b','opcion2','opcion_2','opcion-2'],
            'c'         => ['c','opcion_c','opcion3','opcion_3','opcion-3'],
            'd'         => ['d','opcion_d','opcion4','opcion_4','opcion-4'],
            'correcta'  => ['correcta','respuesta','opcion_correcta','correct','answer','correct_index'],
            // Opcional: si tu categoría viene por ID en lugar de nombre
            'category_id' => ['category_id','categoria_id','id_categoria'],
        ];

        $idx = $this->indexHeaders($headers, $map);

        // Validar que existan columnas mínimas
        $minCols = ['texto','category'];
        foreach ($minCols as $col) {
            if (!isset($idx[$col])) {
                return back()->withErrors([
                    'csv' => "Falta la columna requerida '{$col}' (podés usar alias como: ".implode(', ', $map[$col]).")."
                ]);
            }
        }
        // Requiere al menos A, B (y preferible C, D)
        if (!isset($idx['a']) || !isset($idx['b'])) {
            return back()->withErrors(['csv' => 'Faltan columnas para opciones mínimas (A y B).']);
        }

        // Cache de categorías para minimizar queries
        $catCacheByName = Categoria::all(['id','nombre'])
            ->keyBy(fn($c) => Str::lower(trim($c->nombre)));

        $catCacheById = Categoria::all(['id'])->keyBy('id');

        $insertados = 0;
        $actualizados = 0;
        $errores = [];

        DB::beginTransaction();
        try {
            $rowNum = 1; // ya leímos cabecera
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNum++;

                // Saltar filas vacías
                if ($this->rowIsEmpty($row)) {
                    continue;
                }

                // Helper para tomar un valor por índice si existe
                $val = function($key) use ($idx, $row) {
                    return isset($idx[$key]) && isset($row[$idx[$key]]) ? trim((string)$row[$idx[$key]]) : null;
                };

                $texto         = $val('texto');
                $categoryName  = $val('category');
                $categoryIdCSV = $val('category_id');

                $a = $val('a'); $b = $val('b'); $c = $val('c'); $d = $val('d');
                $correctRaw = $val('correcta'); // Puede ser A/B/C/D, índice (0-3/1-4), o el texto exacto.

                // Validaciones básicas por fila
                if (!$texto) {
                    $errores[] = "Fila {$rowNum}: falta 'texto' de la pregunta.";
                    continue;
                }

                // Resolver categoría (por ID explícito o por nombre)
                $categoryId = null;
                if ($categoryIdCSV && isset($catCacheById[$categoryIdCSV])) {
                    $categoryId = (int)$categoryIdCSV;
                } else {
                    if (!$categoryName) {
                        $errores[] = "Fila {$rowNum} ('{$texto}'): falta 'categoria'.";
                        continue;
                    }
                    $key = Str::lower(trim($categoryName));
                    if (!isset($catCacheByName[$key])) {
                        if ($crearCategorias) {
                            $new = Categoria::create(['nombre' => $categoryName]);
                            $catCacheByName[$key] = $new;
                            $catCacheById[$new->id] = $new;
                            $categoryId = $new->id;
                        } else {
                            $errores[] = "Fila {$rowNum} ('{$texto}'): la categoría '{$categoryName}' no existe.";
                            continue;
                        }
                    } else {
                        $categoryId = $catCacheByName[$key]->id;
                    }
                }

                // Mapear correcta
                $opciones = ['A' => $a, 'B' => $b, 'C' => $c, 'D' => $d];
                [$opcionCorrecta, $correctIndex] = $this->resolveCorrect($correctRaw, $opciones);

                if (!$opcionCorrecta) {
                    $errores[] = "Fila {$rowNum} ('{$texto}'): no se pudo determinar la opción correcta (valor '{$correctRaw}').";
                    continue;
                }

                // Armar payload
                $payload = [
                    'texto'            => $texto,
                    'category_id'      => $categoryId,
                    'opcion_correcta'  => $opcionCorrecta,
                    'opcion_1'         => $b, // en tu modelo: opcion_1/2/3 acompañan a 'opcion_correcta' (A)
                    'opcion_2'         => $c,
                    'opcion_3'         => $d,
                    'correct_index'    => $correctIndex, // 0=A, 1=B, 2=C, 3=D
                    'is_active'        => false,
                ];

                // Nota: guardamos 'A' en opcion_correcta como en tu modelo.
                // Si preferís que 'A' no siempre sea la correcta, usamos el índice detectado:
                // Reordenar para que opcion_correcta sea el texto correcto y las demás queden en 1..3.
                $payload = $this->normalizeOptionsToABCD($payload, $a, $b, $c, $d, $correctIndex);

                if ($modo === 'upsert') {
                    // upsert por (texto + category_id)
                    $q = Question::updateOrCreate(
                        ['texto' => $payload['texto'], 'category_id' => $payload['category_id']],
                        $payload
                    );
                    $q->wasRecentlyCreated ? $insertados++ : $actualizados++;
                } else {
                    // insert puro; evitar duplicados simples
                    $exists = Question::where('texto', $payload['texto'])
                        ->where('category_id', $payload['category_id'])
                        ->exists();

                    if ($exists) {
                        $errores[] = "Fila {$rowNum} ('{$texto}'): ya existe una pregunta igual en esa categoría.";
                        continue;
                    }
                    Question::create($payload);
                    $insertados++;
                }
            }

            fclose($handle);
            DB::commit();

            return back()->with('status', "Importación finalizada: {$insertados} nuevas, {$actualizados} actualizadas, ".count($errores)." con error.")
                         ->with('import_errors', $errores);

        } catch (\Throwable $e) {
            if (is_resource($handle)) fclose($handle);
            DB::rollBack();
            report($e);
            return back()->withErrors(['csv' => 'Error procesando el archivo: '.$e->getMessage()]);
        }
    }

    private function guessDelimiter(string $line): string
    {
        $candidates = [",",";","\t","|"];
        $best = ","; $max = 0;
        foreach ($candidates as $d) {
            $count = substr_count($line, $d);
            if ($count > $max) { $max = $count; $best = $d; }
        }
        return $best;
    }

    private function normalizeHeader(?string $h): string
    {
        $h = Str::of((string)$h)->lower()->replace(['-', ' ', '.'], ['_','_',''])->value();
        return trim($h);
    }

    private function indexHeaders(array $headers, array $map): array
    {
        $idx = [];
        foreach ($map as $key => $aliases) {
            foreach ($aliases as $alias) {
                $aliasNorm = $this->normalizeHeader($alias);
                $pos = array_search($aliasNorm, $headers, true);
                if ($pos !== false) {
                    $idx[$key] = $pos;
                    break;
                }
            }
        }
        return $idx;
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $v) {
            if (trim((string)$v) !== '') return false;
        }
        return true;
    }

    /**
     * @return array [opcionCorrectaTexto|null, correctIndex(0..3)|null]
     */
    private function resolveCorrect(?string $raw, array $opciones): array
    {
        if ($raw === null || $raw === '') return [null, null];

        $r = Str::upper(trim($raw));

        // Caso: A/B/C/D
        $map = ['A'=>0,'B'=>1,'C'=>2,'D'=>3];
        if (isset($map[$r])) {
            $i = $map[$r];
            $labels = array_keys($opciones);
            $label = $labels[$i] ?? 'A';
            $texto = $opciones[$label] ?? null;
            return [$texto, $i];
        }

        // Caso: 1..4 o 0..3
        if (is_numeric($r)) {
            $n = (int)$r;
            if ($n >= 1 && $n <= 4) { $i = $n - 1; }
            elseif ($n >= 0 && $n <= 3) { $i = $n; }
            else { return [null, null]; }
            $labels = array_keys($opciones);
            $label = $labels[$i] ?? 'A';
            return [$opciones[$label] ?? null, $i];
        }

        // Caso: texto exacto (coincide con alguna opción)
        foreach (['A','B','C','D'] as $i => $label) {
            if (isset($opciones[$label]) && trim(Str::lower($opciones[$label])) === trim(Str::lower($raw))) {
                return [$opciones[$label], $i];
            }
        }

        return [null, null];
    }

    /**
     * Normaliza para que:
     * - 'opcion_correcta' sea el texto correcto
     * - 'opcion_1..3' contengan el resto en orden B,C,D (según tu esquema actual)
     */
    private function normalizeOptionsToABCD(array $payload, ?string $a, ?string $b, ?string $c, ?string $d, int $correctIndex): array
    {
        $all = [$a, $b, $c, $d];
        $correctText = $all[$correctIndex] ?? $a;

        // Resto sin la correcta, en el orden original A..D
        $rest = [];
        foreach ($all as $k => $opt) {
            if ($k !== $correctIndex) $rest[] = $opt;
        }

        $payload['opcion_correcta'] = $correctText;
        $payload['opcion_1'] = $rest[0] ?? null;
        $payload['opcion_2'] = $rest[1] ?? null;
        $payload['opcion_3'] = $rest[2] ?? null;

        return $payload;
    }
}

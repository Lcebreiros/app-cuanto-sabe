<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Motivo;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestionImportController extends Controller
{
    public function create()
    {
        $motivos = Motivo::orderBy('nombre')->get();
        return view('questions.import', compact('motivos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv' => ['required','file','mimes:csv,txt','max:10240'],
            'crear_categorias' => ['nullable','boolean'],
            'crear_motivos' => ['nullable','boolean'],
            'motivo_forzado_id' => ['nullable','exists:motivos,id'],
            'modo' => ['required','in:insert,upsert'],
        ]);

        $crearCategorias = (bool)$request->boolean('crear_categorias');
        $crearMotivos = (bool)$request->boolean('crear_motivos');
        $motivoForzadoId = $request->input('motivo_forzado_id');
        $modo = $request->input('modo', 'insert');

        // Leer el archivo con manejo UTF-8
        $path = $request->file('csv')->getRealPath();
        $content = file_get_contents($path);
        
        // Remover BOM si existe
        $content = str_replace("\xEF\xBB\xBF", '', $content);
        
        // Detectar y convertir encoding si es necesario
        if (!mb_check_encoding($content, 'UTF-8')) {
            $detected = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
            if ($detected) {
                $content = mb_convert_encoding($content, 'UTF-8', $detected);
            }
        }
        
        // Crear un recurso temporal en memoria con el contenido UTF-8
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);
        
        if (!$handle) {
            return back()->withErrors(['csv' => 'No se pudo abrir el archivo.']);
        }

        $firstLine = fgets($handle);
        $delimiter = $this->guessDelimiter($firstLine);
        rewind($handle);

        // Configurar locale para el parser CSV
        setlocale(LC_ALL, 'en_US.UTF-8');

        $headers = fgetcsv($handle, 0, $delimiter, '"', '\\');
        if (!$headers) {
            fclose($handle);
            return back()->withErrors(['csv' => 'El CSV está vacío o no tiene cabecera.']);
        }

        $headers = array_map(fn($h) => $this->normalizeHeader($h), $headers);

        $map = [
            'motivo'    => ['motivo','motivo_nombre','reason','tema'],
            'category'  => ['categoria','category','cat','categoria_nombre','category_name'],
            'texto'     => ['pregunta','texto','enunciado','question'],
            'a'         => ['a','opcion_a','opcion1','opcion_1','opcion-1'],
            'b'         => ['b','opcion_b','opcion2','opcion_2','opcion-2'],
            'c'         => ['c','opcion_c','opcion3','opcion_3','opcion-3'],
            'd'         => ['d','opcion_d','opcion4','opcion_4','opcion-4'],
            'correcta'  => ['correcta','respuesta','opcion_correcta','correct','answer','correct_index'],
            'motivo_id' => ['motivo_id','id_motivo'],
            'category_id' => ['category_id','categoria_id','id_categoria'],
        ];

        $idx = $this->indexHeaders($headers, $map);

        // Validaciones mínimas
        $minCols = ['texto','category'];
        foreach ($minCols as $col) {
            if (!isset($idx[$col])) {
                fclose($handle);
                return back()->withErrors([
                    'csv' => "Falta la columna requerida '{$col}' (alias: ".implode(', ', $map[$col]).")."
                ]);
            }
        }

        if (!isset($idx['a']) || !isset($idx['b'])) {
            fclose($handle);
            return back()->withErrors(['csv' => 'Faltan columnas para opciones mínimas (A y B).']);
        }

        // Si no hay motivo forzado y tampoco columna motivo en CSV, error
        if (!$motivoForzadoId && !isset($idx['motivo']) && !isset($idx['motivo_id'])) {
            fclose($handle);
            return back()->withErrors([
                'csv' => 'Debes seleccionar un motivo en el formulario O incluir la columna "motivo" en el CSV.'
            ]);
        }

        // Caches
        $motivoCacheByName = Motivo::all(['id','nombre'])
            ->keyBy(fn($m) => Str::lower(trim($m->nombre)));
        $motivoCacheById = Motivo::all(['id'])->keyBy('id');

        $catCacheByName = Categoria::all(['id','nombre','motivo_id'])
            ->keyBy(fn($c) => Str::lower(trim($c->nombre)).'-'.$c->motivo_id);

        $catCacheById = Categoria::all(['id'])->keyBy('id');

        $insertados = 0;
        $actualizados = 0;
        $errores = [];
        $motivosCreados = [];
        $categoriasCreadas = [];

        DB::beginTransaction();
        try {
            $rowNum = 1;
            while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
                $rowNum++;

                if ($this->rowIsEmpty($row)) {
                    continue;
                }

                $val = function($key) use ($idx, $row) {
                    $value = isset($idx[$key]) && isset($row[$idx[$key]]) ? trim((string)$row[$idx[$key]]) : null;
                    // Sanitizar para prevenir problemas de encoding
                    return $this->sanitizeText($value);
                };

                $texto         = $val('texto');
                $motivoName    = $val('motivo');
                $motivoIdCSV   = $val('motivo_id');
                $categoryName  = $val('category');
                $categoryIdCSV = $val('category_id');

                $a = $val('a'); $b = $val('b'); $c = $val('c'); $d = $val('d');
                $correctRaw = $val('correcta');

                if (!$texto) {
                    $errores[] = "Fila {$rowNum}: falta 'texto' de la pregunta.";
                    continue;
                }

                // ===== RESOLVER MOTIVO =====
                $motivoId = null;

                // PRIORIDAD 1: Motivo forzado desde el front
                if ($motivoForzadoId) {
                    $motivoId = (int)$motivoForzadoId;
                }
                // PRIORIDAD 2: motivo_id del CSV
                elseif ($motivoIdCSV && isset($motivoCacheById[$motivoIdCSV])) {
                    $motivoId = (int)$motivoIdCSV;
                }
                // PRIORIDAD 3: nombre del motivo del CSV
                elseif ($motivoName) {
                    $keyMotivo = Str::lower(trim($motivoName));
                    if (!isset($motivoCacheByName[$keyMotivo])) {
                        if ($crearMotivos) {
                            $newMotivo = Motivo::create(['nombre' => $motivoName]);
                            $motivoCacheByName[$keyMotivo] = $newMotivo;
                            $motivoCacheById[$newMotivo->id] = $newMotivo;
                            $motivoId = $newMotivo->id;
                            $motivosCreados[] = $motivoName;
                        } else {
                            $errores[] = "Fila {$rowNum} ('{$texto}'): el motivo '{$motivoName}' no existe y crear_motivos está desactivado.";
                            continue;
                        }
                    } else {
                        $motivoId = $motivoCacheByName[$keyMotivo]->id;
                    }
                }

                if (!$motivoId) {
                    $errores[] = "Fila {$rowNum} ('{$texto}'): no se pudo determinar el motivo.";
                    continue;
                }

                // ===== RESOLVER CATEGORÍA =====
                $categoryId = null;

                if ($categoryIdCSV && isset($catCacheById[$categoryIdCSV])) {
                    $categoryId = (int)$categoryIdCSV;
                } else {
                    if (!$categoryName) {
                        $errores[] = "Fila {$rowNum} ('{$texto}'): falta 'categoria'.";
                        continue;
                    }
                    
                    // Key único: nombre + motivo_id (permite misma categoría en distintos motivos)
                    $keyCat = Str::lower(trim($categoryName)).'-'.$motivoId;
                    
                    if (!isset($catCacheByName[$keyCat])) {
                        if ($crearCategorias) {
                            $newCat = Categoria::create([
                                'nombre' => $categoryName,
                                'motivo_id' => $motivoId
                            ]);
                            $catCacheByName[$keyCat] = $newCat;
                            $catCacheById[$newCat->id] = $newCat;
                            $categoryId = $newCat->id;
                            $categoriasCreadas[] = "{$categoryName} (Motivo: ".($motivoCacheById[$motivoId]->nombre ?? $motivoId).")";
                        } else {
                            $errores[] = "Fila {$rowNum} ('{$texto}'): la categoría '{$categoryName}' no existe en el motivo especificado.";
                            continue;
                        }
                    } else {
                        $categoryId = $catCacheByName[$keyCat]->id;
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
                    'opcion_1'         => $b,
                    'opcion_2'         => $c,
                    'opcion_3'         => $d,
                    'correct_index'    => $correctIndex,
                    'is_active'        => false,
                ];

                $payload = $this->normalizeOptionsToABCD($payload, $a, $b, $c, $d, $correctIndex);

                if ($modo === 'upsert') {
                    $q = Question::updateOrCreate(
                        ['texto' => $payload['texto'], 'category_id' => $payload['category_id']],
                        $payload
                    );
                    $q->wasRecentlyCreated ? $insertados++ : $actualizados++;
                } else {
                    $exists = Question::where('texto', $payload['texto'])
                        ->where('category_id', $payload['category_id'])
                        ->exists();

                    if ($exists) {
                        $errores[] = "Fila {$rowNum} ('{$texto}'): ya existe.";
                        continue;
                    }
                    Question::create($payload);
                    $insertados++;
                }
            }

            fclose($handle);
            DB::commit();

            $mensaje = "✅ Importación finalizada: {$insertados} nuevas, {$actualizados} actualizadas";
            if (!empty($motivosCreados)) {
                $mensaje .= " | Motivos creados: ".implode(', ', array_unique($motivosCreados));
            }
            if (!empty($categoriasCreadas)) {
                $mensaje .= " | Categorías creadas: ".implode(', ', array_unique($categoriasCreadas));
            }
            if (!empty($errores)) {
                $mensaje .= " | ".count($errores)." con error";
            }

            return back()->with('status', $mensaje)
                         ->with('import_errors', $errores);

        } catch (\Throwable $e) {
            if (is_resource($handle)) fclose($handle);
            DB::rollBack();
            report($e);
            return back()->withErrors(['csv' => 'Error: '.$e->getMessage()]);
        }
    }

    /**
     * Sanitiza y normaliza texto para prevenir problemas de encoding
     * Actúa como safety net en caso de que la configuración UTF-8 falle
     */
    private function sanitizeText(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }
        
        // Solo remover caracteres de control realmente problemáticos (NULL, etc)
        // NO remover tabs ni newlines normales que podrían estar en el contenido
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);
        
        // Remover espacios no-break Unicode y zero-width spaces
        $text = str_replace(["\xC2\xA0", "\xE2\x80\x8B", "\xE2\x80\x8C", "\xE2\x80\x8D"], ' ', $text);
        
        // Normalizar múltiples espacios/tabs/newlines a un solo espacio
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Asegurar UTF-8 válido solo si realmente hay un problema
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }
        
        return trim($text);
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

    private function resolveCorrect(?string $raw, array $opciones): array
    {
        if ($raw === null || $raw === '') return [null, null];

        $r = Str::upper(trim($raw));

        $map = ['A'=>0,'B'=>1,'C'=>2,'D'=>3];
        if (isset($map[$r])) {
            $i = $map[$r];
            $labels = array_keys($opciones);
            $label = $labels[$i] ?? 'A';
            $texto = $opciones[$label] ?? null;
            return [$texto, $i];
        }

        if (is_numeric($r)) {
            $n = (int)$r;
            if ($n >= 1 && $n <= 4) { $i = $n - 1; }
            elseif ($n >= 0 && $n <= 3) { $i = $n; }
            else { return [null, null]; }
            $labels = array_keys($opciones);
            $label = $labels[$i] ?? 'A';
            return [$opciones[$label] ?? null, $i];
        }

        foreach (['A','B','C','D'] as $i => $label) {
            if (isset($opciones[$label]) && trim(Str::lower($opciones[$label])) === trim(Str::lower($raw))) {
                return [$opciones[$label], $i];
            }
        }

        return [null, null];
    }

    private function normalizeOptionsToABCD(array $payload, ?string $a, ?string $b, ?string $c, ?string $d, int $correctIndex): array
    {
        $all = [$a, $b, $c, $d];
        $correctText = $all[$correctIndex] ?? $a;

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
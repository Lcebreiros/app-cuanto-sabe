<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameSession;
use App\Models\Question;
use App\Events\NuevaPreguntaOverlay;
use App\Events\OpcionSeleccionada;
use App\Events\ParticipantQueueUpdated;
use App\Models\ParticipantSession;
use App\Models\Motivo;
use App\Models\Categoria;
use Illuminate\Support\Facades\Cookie;
use App\Models\ParticipantAnswer;
use Illuminate\Support\Facades\DB;
use App\Services\GamePointsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GameSessionController extends Controller
{
        protected function getActiveSessionCached($ttlSeconds = 8)
    {
        return Cache::remember("game_session_active", $ttlSeconds, function () {
            return GameSession::where('status', 'active')->latest()->first();
        });
    }
    public function start(Request $request)
{
    $request->validate([
        'guest_name' => 'required|string|max:50',
        'motivo_id' => 'required|exists:motivos,id',
        'modo_juego' => 'required|in:normal,express',
    ]);

    GameSession::where('status', 'active')->update(['status' => 'ended']);

    $session = GameSession::create([
        'guest_name' => $request->guest_name,
        'motivo_id' => $request->motivo_id,
        'status' => 'active',
        'modo_juego' => $request->modo_juego,
        // inicializar contadores explícitamente
        'apuesta_x2_active' => false,
        'apuesta_x2_usadas' => 0,
        'descarte_usados' => 0,
    ]);

    \Cache::forget('game_session_active');

    $modoTexto = $session->isExpress() ? 'Express (10 pts)' : 'Normal (25 pts)';
    return redirect()->back()->with('success', "¡Juego iniciado para {$session->guest_name} en modo {$modoTexto}!");
}

    public function end()
{
    // Encuentra la sesión activa
    $sessions = GameSession::where('status', 'active')->get();
    foreach ($sessions as $session) {
        // Borra todas las respuestas de los participantes de esa sesión
        $participantIds = $session->participants()->pluck('id');
        \App\Models\ParticipantAnswer::whereIn('participant_session_id', $participantIds)->delete();
        // Borra los participantes
        \App\Models\ParticipantSession::whereIn('id', $participantIds)->delete();
    }
    GameSession::where('status', 'active')->update(['status' => 'ended']);
    return redirect()->back()->with('success', 'Juego finalizado y participantes eliminados.');
}

public function cambiarModo(Request $request, $id)
{
    $request->validate([
        'modo_juego' => 'required|in:normal,express',
    ]);

    $gameSession = GameSession::findOrFail($id);

    $gameSession->modo_juego = $request->modo_juego;
    $gameSession->save();

    // Ejemplo de uso de los helpers para lógica adicional
    if ($gameSession->isExpress()) {
        // lógica específica para modo express
    } elseif ($gameSession->isNormal()) {
        // lógica específica para modo normal
    }

    return response()->json([
        'message' => 'Modo de juego actualizado con éxito',
        'data' => $gameSession
    ]);
}


public function revealAnswer(Request $request)
{
    try {
        $data = session('last_overlay_question', null);
        \Log::info('🔴 REVEAL: Sesión PHP', ['data' => $data]);
        
        if (!$data) {
            $session = $this->getActiveSessionCached(5);
            if ($session && $session->pregunta_json) {
                $data = json_decode($session->pregunta_json, true);
                \Log::info('🟡 REVEAL: Recuperado de BD', ['data' => $data]);
                session(['last_overlay_question' => $data]);
            }
        }
        
        if (!$data) {
            \Log::warning('🔴 REVEAL: No hay pregunta activa');
            return response()->json(['error' => 'No hay pregunta activa en sesión'], 400);
        }

        $session = $this->getActiveSessionCached(5);
        if (!$session) {
            return response()->json(['error' => 'No hay sesión activa'], 400);
        }

        $gamePoints = app(\App\Services\GamePointsService::class);

        $delta = 0;
        $tendencia = null;

        $selectedOption = session('selected_guest_option', null);

        if ($selectedOption && isset($data['label_correcto'], $data['pregunta_id'])) {
            // Calcular puntaje invitado
            $delta = $gamePoints->calcularPuntajeInvitado(
                $session->id,
                $selectedOption,
                $data['pregunta_id'],
                $data['label_correcto'],
                false,
                false,
                null
            );

            // Actualizar puntos del invitado
            $session->guest_points = ($session->guest_points ?? 0) + (int)$delta;
            $session->save();

            // Emitir evento para actualizar puntaje en vivo
            broadcast(new \App\Events\GuestPointsUpdated($session->id, $session->guest_points));

            session()->forget('selected_guest_option');

            // Calcular tendencia del público
            $tendencia = $gamePoints->calcularTendencia($session->id, $data['pregunta_id']);
        }

        // ✅ PUNTAJES DE PARTICIPANTES - INDIVIDUAL
        $participantIds = $session->participants()->pluck('id')->toArray();
        $puntajes = $gamePoints->calcularPuntajesParticipantes($participantIds, $session->id);

        // ✅ GUARDAR PUNTAJES EN BASE DE DATOS
        foreach ($puntajes as $participantId => $puntajeTotal) {
            \App\Models\ParticipantSession::where('id', $participantId)
                ->update(['puntaje' => $puntajeTotal]);
            
            \Log::info('💾 Puntaje guardado en BD', [
                'participant_id' => $participantId,
                'puntaje' => $puntajeTotal
            ]);
        }

        // ✅ DISPARAR EVENTO INDIVIDUAL PARA CADA PARTICIPANTE
        foreach ($puntajes as $participantId => $puntajeTotal) {
            \Log::info('📤 Enviando PuntajeActualizado', [
                'participant_id' => $participantId,
                'puntaje' => $puntajeTotal,
                'canal' => 'puntaje.' . $participantId
            ]);
            
            broadcast(new \App\Events\PuntajeActualizado($participantId, $puntajeTotal));
        }

        // Racha del público
        $rachaPublico = $gamePoints->verificarTendenciaPublico($session->id);

        // Verificar victoria del invitado
        $victoria = $gamePoints->verificarVictoriaInvitado($session->id);

        // ✅ Broadcast general con toda la data necesaria
        broadcast(new \App\Events\RevealAnswerOverlay([
            'pregunta_id' => $data['pregunta_id'],
            'label_correcto' => $data['label_correcto'],
            'opciones' => $data['opciones'] ?? [],
            'pregunta' => $data['pregunta'] ?? '',
            'delta_invitado' => $delta,
            'puntaje_invitado' => $session->guest_points,
            'puntajes_participantes' => $puntajes, // Para el overlay del host
            'tendencia' => $tendencia,
            'racha_publico' => $rachaPublico,
            'victoria' => $victoria,
            'golden' => ($data['special_indicator'] ?? null) === 'PREGUNTA DE ORO',
        ]));

        \Log::info('✅ REVEAL: Completado exitosamente', [
            'participantes_notificados' => count($puntajes)
        ]);
        
        return response()->json(['success' => true]);
        
    } catch (\Throwable $e) {
        \Log::error('❌ Error en revealAnswer: '.$e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'Ocurrió un error al procesar la petición'], 500);
    }
}

    public function sendRandomQuestion(Request $request)
    {
        $request->validate([
            'categoria_id' => 'required|exists:categorias,id'
        ]);

        $lastQuestionId = session('last_random_question_id');
        $pregunta = Question::where('category_id', $request->categoria_id)
                            ->where('id', '!=', $lastQuestionId)
                            ->inRandomOrder()
                            ->first();

        if(!$pregunta) {
            return response()->json(['error' => 'No hay preguntas disponibles en esta categoría.'], 404);
        }

        $opciones = [
            ['text' => $pregunta->opcion_correcta],
            ['text' => $pregunta->opcion_1],
            ['text' => $pregunta->opcion_2],
            ['text' => $pregunta->opcion_3],
        ];
        shuffle($opciones);

        $data_opciones = [];
        $label_correcto = null;
        foreach ($opciones as $i => $op) {
            $label = chr(65+$i); // A, B, C, D
            $data_opciones[] = [
                'label' => $label,
                'texto' => $op['text'],
            ];
            if ($label_correcto === null && $op['text'] === $pregunta->opcion_correcta) {
                $label_correcto = $label;
            }
        }

        $data = [
            'pregunta' => $pregunta->texto,
            'opciones' => $data_opciones,
            'label_correcto' => $label_correcto,
            'pregunta_id' => $pregunta->id,
            'categoria_id' => $request->categoria_id,
            'timestamp' => now()->toISOString(),
        ];

        session([
            'last_overlay_question' => $data, // 🟢 CORREGIDO
            'last_random_question_id' => $pregunta->id
        ]);

        broadcast(new NuevaPreguntaOverlay($data));
        return response()->json([
            'success' => true,
            'mensaje' => 'Pregunta enviada',
            'data' => $data
        ]);
    }

public function selectOption(Request $request)
{
    $opcion = $request->input('opcion');
    session(['selected_guest_option' => $opcion]); // <--- NUEVO

    broadcast(new \App\Events\OpcionSeleccionada($opcion))->toOthers();
    return response()->json(['ok' => true]);
}


public function overlayReset(Request $request)
{
    $session = $this->getActiveSessionCached(5);

    if ($session) {
        if ($session->active_question_id !== null || $session->pregunta_json !== null) {
            $session->active_question_id = null;
            $session->pregunta_json = null;
            $session->save();
            Cache::forget("game_session_active");

            // broadcast solo si hubo write real
            broadcast(new \App\Events\OverlayReset());
        }
    }

    return response()->json(['success' => true]);
}




    public function queueList($sessionId)
    {
        $session = GameSession::findOrFail($sessionId);
        $participants = $session->participants()->orderBy('order')->get();

        if (request()->ajax() || request()->wantsJson()) {
            return view('components.queue-list', compact('participants', 'session'))->render();
        }
        return view('components.queue-list', compact('participants', 'session'));
    }

public function add(Request $request)
{
    $validated = $request->validate([
        'participants.0.username' => 'required|string|max:30',
        'participants.0.dni_last4' => 'required|digits:4',
    ]);

    $session = GameSession::where('status', 'active')->latest()->first();
    if(!$session) {
        return back()->with('error', 'No hay sesión activa.');
    }

    $existingParticipant = ParticipantSession::where('game_session_id', $session->id)
        ->where('username', $validated['participants'][0]['username'])
        ->where('dni_last4', $validated['participants'][0]['dni_last4'])
        ->first();

    if($existingParticipant) {
        // GUARDÁ EL PARTICIPANTE EN SESIÓN Y COOKIE
        session(['participant_session_id' => $existingParticipant->id]);
        Cookie::queue('participant_session_id', $existingParticipant->id, 60*24*30); // 30 días

        // 🔁 Redirección inteligente
        $returnToUrl = session('return_to_url');
        session()->forget('return_to_url');
        if ($returnToUrl) {
            return redirect($returnToUrl)->with('success', 'Ya estás registrado en esta sesión.');
        }

        return back()->with('success', 'Ya estás registrado en esta sesión.');
    }

    $participant = new ParticipantSession([
        'username' => $validated['participants'][0]['username'],
        'dni_last4' => $validated['participants'][0]['dni_last4'],
        'game_session_id' => $session->id,
        'status' => 'waiting',
        'order' => $session->participants()->max('order') + 1,
    ]);
    $participant->save();

    // GUARDÁ EL NUEVO PARTICIPANTE EN SESIÓN Y COOKIE
    session(['participant_session_id' => $participant->id]);
    Cookie::queue('participant_session_id', $participant->id, 60*24*30); // 30 días

    broadcast(new ParticipantQueueUpdated($session->id));

    // 🔁 Redirección inteligente
    $returnToUrl = session('return_to_url');
    session()->forget('return_to_url');
    if ($returnToUrl) {
        return redirect($returnToUrl)->with('success', '¡Te anotaste en la cola!');
    }
    // 🔁 Redirección por redirect_after_participant_login
    $redirect = session('redirect_after_participant_login');
    if ($redirect) {
        session()->forget('redirect_after_participant_login');
        return redirect()->route($redirect)->with('success', '¡Sesión iniciada! Ya podés jugar.');
    }
    return back()->with('success', '¡Te anotaste en la cola!');
    }

public function ruletaOverlay()
{
    $session = GameSession::where('status', 'active')->latest()->first();
    if (!$session) {
        return back()->with('error', 'No hay sesión activa.');
    }

    $motivo = Motivo::find($session->motivo_id);
    $categorias = $motivo->categorias ?? collect();

    $categories = $categorias->map(function($cat) {
        return [
            'label' => $cat->nombre,
            'color' => $cat->color ?? "#2346c0",
            'textColor' => $cat->text_color ?? "#fff",
            'fixed' => false,
        ];
    })->values()->toArray();

    array_unshift($categories,
        ['label' => "Pregunta de oro", 'color' => "#ffe47a", 'textColor' => "#ad8100", 'fixed' => true],
        ['label' => "Responde el chat", 'color' => "#02204e", 'textColor' => "#00f0ff", 'fixed' => true],
        ['label' => "Solo yo", 'color' => "#101e33", 'textColor' => "#19ff8c", 'fixed' => true],
        ['label' => "Random", 'color' => "#0e223c", 'textColor' => "#ffe47a", 'fixed' => true]
    );

    $sessionGame = ['categories' => $categories];

    // ✅ definirla aquí
    $activeSession = $session;

    return view('overlay', compact('sessionGame', 'activeSession'));
}


public function lanzarPreguntaCategoria(Request $request)
{
    $categoria = $request->input('categoria');
    $categoriaLower = strtolower($categoria);
    $specialSlot = $request->input('special_slot');

    $session = GameSession::where('status', 'active')->latest()->first();
    if (!$session) {
        return response()->json(['error' => 'No hay sesión activa'], 400);
    }

    // ✅ Obtener ID de la pregunta anterior para no repetirla
    $preguntaAnteriorId = $session->active_question_id;

    // Si es random, buscar una categoría random del motivo
    if ($categoriaLower === 'random') {
        $motivo = Motivo::find($session->motivo_id);
        $categorias = $motivo && $motivo->categorias->count() > 0 ? $motivo->categorias : collect();
        if ($categorias->isEmpty()) {
            return response()->json(['error' => 'No hay categorías disponibles para random'], 404);
        }
        $categoriaModel = $categorias->random();
    } 
    // Pregunta de oro
    elseif ($categoriaLower === 'pregunta de oro') {
        $data = [
            'pregunta' => strtoupper($categoria),
            'opciones' => [],
            'label_correcto' => null,
            'pregunta_id' => null,
            'categoria_id' => null,
            'categoria_nombre' => 'Pregunta de Oro', // ✅ AGREGADO
            'timestamp' => now()->toISOString(),
            'special_indicator' => $specialSlot ?? strtoupper($categoria),
        ];
        $session->active_question_id = null;
        $session->pregunta_json = null;
        $session->save();
        
        session(['last_overlay_question' => $data]);
        
        broadcast(new NuevaPreguntaOverlay($data));
        return response()->json(['ok' => true]);
    }
    // Chat o Solo Yo
    elseif ($categoriaLower === 'responde el chat' || $categoriaLower === 'solo yo') {
        // ✅ Diferenciar: "Solo yo" deshabilita público, "Responde el chat" no
        $disablePublic = ($categoriaLower === 'solo yo');
        
        $data = [
            'pregunta' => strtoupper($categoria),
            'opciones' => [],
            'label_correcto' => null,
            'pregunta_id' => null,
            'categoria_id' => null,
            'categoria_nombre' => ucwords($categoria), // ✅ AGREGADO (capitaliza: "Solo Yo", "Responde El Chat")
            'timestamp' => now()->toISOString(),
            'special_indicator' => $specialSlot ?? strtoupper($categoria),
            'disable_public_answers' => $disablePublic,
        ];
        $session->active_question_id = null;
        $session->pregunta_json = null;
        $session->save();
        
        session(['last_overlay_question' => $data]);
        
        broadcast(new NuevaPreguntaOverlay($data));
        return response()->json(['ok' => true]);
    }
    // Si es categoría normal
    else {
        $categoriaModel = Categoria::where('nombre', $categoria)->first();
        if (!$categoriaModel) {
            return response()->json(['error' => 'Categoría no encontrada: '.$categoria], 404);
        }
    }

    // ✅ BUSCAR PREGUNTA ALEATORIA EXCLUYENDO LA ANTERIOR
    $query = Question::where('category_id', $categoriaModel->id);
    
    // Excluir la pregunta anterior si existe
    if ($preguntaAnteriorId) {
        $query->where('id', '!=', $preguntaAnteriorId);
    }
    
    $pregunta = $query->inRandomOrder()->first();
    
    // Si no hay más preguntas (solo había 1 y era la anterior), permitir repetirla
    if (!$pregunta) {
        \Log::warning('⚠️ No hay más preguntas disponibles, permitiendo repetir');
        $pregunta = Question::where('category_id', $categoriaModel->id)->inRandomOrder()->first();
    }
    
    if (!$pregunta) {
        return response()->json(['error' => 'No hay preguntas disponibles en esta categoría: '.$categoriaModel->nombre], 404);
    }

    // Armar opciones y mezclar solo una vez
    $opciones = [
        ['text' => $pregunta->opcion_correcta],
        ['text' => $pregunta->opcion_1],
        ['text' => $pregunta->opcion_2],
        ['text' => $pregunta->opcion_3],
    ];
    shuffle($opciones);

    $data_opciones = [];
    $label_correcto = null;
    foreach ($opciones as $i => $op) {
        $label = chr(65 + $i);
        $data_opciones[] = [
            'label' => $label,
            'texto' => $op['text'],
        ];
        if ($label_correcto === null && $op['text'] === $pregunta->opcion_correcta) {
            $label_correcto = $label;
        }
    }

    $data = [
        'pregunta' => $pregunta->texto,
        'opciones' => $data_opciones,
        'label_correcto' => $label_correcto,
        'pregunta_id' => $pregunta->id,
        'categoria_id' => $pregunta->category_id,
        'categoria_nombre' => $categoriaModel->nombre, // ✅ AGREGADO - Este es el más importante
        'timestamp' => now()->toISOString(),
    ];

    if ($specialSlot) {
        $data['special_indicator'] = $specialSlot;
    }

    // Guardar la pregunta en la sesión activa (BD)
    $session->active_question_id = $pregunta->id;
    $session->pregunta_json = json_encode($data);
    $session->save();
    
    session(['last_overlay_question' => $data]);
    
    \Log::info('🟢 PREGUNTA GUARDADA', [
        'pregunta_id' => $pregunta->id, 
        'label_correcto' => $label_correcto,
        'anterior_id' => $preguntaAnteriorId,
        'categoria' => $categoriaModel->nombre // ✅ AGREGADO al log
    ]);

    broadcast(new NuevaPreguntaOverlay($data));
    return response()->json(['success' => true, 'data' => $data]);
}

    public function girarRuleta() {
        broadcast(new \App\Events\GirarRuleta());
        return response()->json(['ok' => true]);
    }

    public function syncQuestion(Request $request) {
        $data = $request->input('pregunta');
        if ($data) {
            session(['last_overlay_question' => $data]);
            return response()->json(['ok' => true]);
        }
        return response()->json(['ok' => false], 400);
    }

public function participar(Request $request)
{
    $participantSessionId = session('participant_session_id');
    $participant = $participantSessionId
        ? ParticipantSession::find($participantSessionId)
        : null;

    if (!$participant) {
        session(['return_to_url' => url()->full()]);
        return redirect()->route('participants.form');
    }

    $session = GameSession::where('status', 'active')->latest()->first();

    // 🚩 JAMÁS volver a mezclar ni armar opciones acá. Solo leer lo que está en la BD.
    $data = null;
    if ($session && $session->pregunta_json) {
        $data = json_decode($session->pregunta_json, true);
    }

    // Buscar si ya respondió a la pregunta actual
    $yaRespondio = null;
    if ($participant && isset($data['pregunta_id'])) {
        $yaRespondio = \App\Models\ParticipantAnswer::where('participant_session_id', $participant->id)
            ->where('question_id', $data['pregunta_id'])
            ->first();
    }

    // SIEMPRE calcular puntaje, y si es null, poner en 0
// obtener session/servicio
$session = GameSession::where('status', 'active')->latest()->first();
$gamePoints = app(\App\Services\GamePointsService::class);

if ($participant && $session) {
    // recalculamos puntajes usando el método de lote que ya existe en el service
    $puntajesMap = $gamePoints->calcularPuntajesParticipantes([$participant->id], $session->id);
    // $puntajesMap debería ser algo como [participant_id => ['total'=>X,'detalles'=>...]]
    $puntaje = $puntajesMap[$participant->id] ?? ['total' => 0, 'detalles' => []];
} else {
    $puntaje = ['total' => 0, 'detalles' => []];
}

    // SIEMPRE pasar participant y puntaje a la vista
    return view('participar', [
        'pregunta' => $data,
        'yaRespondio' => $yaRespondio ? $yaRespondio->option_label : null,
        'puntaje' => $puntaje,
        'participant' => $participant,
        'sinPregunta' => !$data,
    ]);
}
public function enviarParticipacion(Request $request)
{
    $request->validate([
        'option_label' => 'required|in:A,B,C,D',
        'question_id' => 'required|exists:questions,id'
    ]);

    // 1. Obtener participante desde la session
    $participantSessionId = session('participant_session_id');
    $participant = $participantSessionId ? ParticipantSession::find($participantSessionId) : null;
    if (!$participant) {
        return redirect()->route('participants.form')->with('error', 'Debes iniciar sesión como participante primero.');
    }

    // 2. Obtener label_correcto de la pregunta_json de la sesión activa
    $labelCorrecto = null;
    $session = GameSession::where('status', 'active')->latest()->first();

    if ($session && $session->pregunta_json) {
        $data = json_decode($session->pregunta_json, true);

        if (isset($data['pregunta_id']) && $data['pregunta_id'] == $request->question_id) {
            $labelCorrecto = $data['label_correcto'] ?? null;
        }
    }

    // 3. Fallback (nunca guardar null)
    if (!$labelCorrecto) {
        $prevAnswer = ParticipantAnswer::where('question_id', $request->question_id)
            ->whereNotNull('label_correcto')
            ->latest('id')
            ->first();
        if ($prevAnswer) {
            $labelCorrecto = $prevAnswer->label_correcto;
        }
    }

    // 4. Fallback final
    if (!$labelCorrecto) {
        $question = \App\Models\Question::find($request->question_id);
        if ($question && method_exists($question, 'getCorrectLabel')) {
            $labelCorrecto = $question->getCorrectLabel();
        }
    }

    // 5. Log para depuración
    \Log::info("GUARDAR RESPUESTA: qid={$request->question_id}, label_correcto={$labelCorrecto}, seleccionada={$request->option_label}");

    // 6. Guardar la respuesta
    ParticipantAnswer::updateOrCreate(
        [
            'participant_session_id' => $participant->id,
            'question_id' => $request->question_id,
        ],
        [
            'option_label' => $request->option_label,
            'label_correcto' => $labelCorrecto,
        ]
    );
    // 7. Tendencia y votos (esto es lo que faltaba, y debe ir sí o sí)
    $questionId = $request->question_id;
    $votedOption = $request->option_label;

    $votes = \App\Models\ParticipantAnswer::where('question_id', $questionId)
        ->select('option_label', DB::raw('count(*) as total'))
        ->groupBy('option_label')
        ->get();

    if ($votes->count() === 1) {
        $trendOption = $votedOption;
        $trendTotal = 1;
    } else {
        $max = $votes->max('total');
        $candidates = $votes->where('total', $max)->pluck('option_label')->toArray();

        if (in_array($votedOption, $candidates)) {
            $trendOption = $votedOption;
        } else {
            $trendOption = $candidates[0];
        }
        $trendTotal = $max;
    }

    if (isset($trendOption)) {
        broadcast(new \App\Events\TendenciaActualizada([
            'question_id' => $questionId,
            'option_label' => $trendOption,
            'total' => $trendTotal,
        ]));
    }

    return redirect()->back()->with('success', '¡Respuesta enviada!');
}


public function apiActiveQuestion()
{
    // 🔥 Aumentar TTL y evitar writes innecesarios
    $session = Cache::remember("game_session_active_question", 8, function () {
        $session = GameSession::where('status', 'active')->latest()->first();
        return $session ? [
            'pregunta_json' => $session->pregunta_json,
            'id' => $session->id
        ] : null;
    });

    if (!$session || !$session['pregunta_json']) {
        return response()->json(['pregunta' => null]);
    }
    
    return response()->json(json_decode($session['pregunta_json'], true));
}

public function limpiarPreguntaParticipante()
{
    session()->forget('last_overlay_question');
    return response()->json(['ok' => true]);
}
// Agregar este método al GameSessionController

public function resetParticipante(Request $request)
{
    $request->validate([
        'question_id' => 'required|exists:questions,id'
    ]);

    $participantSessionId = session('participant_session_id');
    $participant = $participantSessionId ? ParticipantSession::find($participantSessionId) : null;
    
    if (!$participant) {
        return response()->json(['error' => 'Participante no encontrado'], 404);
    }

    // Eliminar la respuesta anterior si existe
    ParticipantAnswer::where('participant_session_id', $participant->id)
        ->where('question_id', $request->question_id)
        ->delete();

    return response()->json(['success' => true]);
}
public function showParticipantForm(Request $request)
{
    // Guarda el redirect sólo si viene en la URL
    if ($request->has('redirect')) {
        session(['redirect_after_participant_login' => $request->input('redirect')]);
    }
    return view('auth.participants-form');
}
public function salirDelJuego(Request $request)
{
    // 1. Obtener el ID del participante desde la sesión (antes de forget)
    $participantSessionId = session('participant_session_id');

    // 2. Borrar la sesión y la cookie
    session()->forget('participant_session_id');
    \Cookie::queue(\Cookie::forget('participant_session_id'));

    // 3. Si existe, eliminar el registro del participante (y opcional: sus respuestas)
    if ($participantSessionId) {
        // Guardar el game_session_id antes de eliminarlo
        $participant = \App\Models\ParticipantSession::find($participantSessionId);
        $sessionId = $participant ? $participant->game_session_id : null;

        // Borra sus respuestas primero, si corresponde
        \App\Models\ParticipantAnswer::where('participant_session_id', $participantSessionId)->delete();
        // Borra el participante de la cola
        \App\Models\ParticipantSession::where('id', $participantSessionId)->delete();

        // **LANZA EL EVENTO AQUÍ**
        if ($sessionId) {
            broadcast(new \App\Events\ParticipantQueueUpdated($sessionId));
        }
    }

    // 4. Redirigir
    return redirect()->route('guest-dashboard')->with('success', 'Saliste del juego y tu registro fue eliminado.');
}
// En GameSessionController:
public function apiGuestPoints()
{
    $points = Cache::remember("guest_points_overlay", 3, function () {
        $session = GameSession::where('status', 'active')->latest()->first();
        return $session ? $session->guest_points ?? 0 : 0;
    });
    
    return response()->json(['points' => $points]);
}
/* public function activarApuestaX2($id)
{
    $session = GameSession::findOrFail($id);

    if ($session->apuesta_x2_active) {
        return response()->json(['ok' => false, 'msg' => 'Ya tienes una apuesta x2 activa']);
    }

    $session->apuesta_x2_active = true;
    $session->save();

    session(['guest_apuesta_x2' => true]);

    return response()->json(['ok' => true, 'msg' => 'Apuesta x2 activada']);
}

public function desactivarApuestaX2($id)
{
    $session = GameSession::findOrFail($id);

    $session->apuesta_x2_active = false;
    $session->save();

    session()->forget('guest_apuesta_x2');

    return response()->json(['ok' => true, 'msg' => 'Apuesta x2 desactivada']);
}

public function usarDescarte($id)
{
    $session = GameSession::findOrFail($id);

    if ($session->descarte_usados >= 3) { // por ejemplo, máximo 3 descartes
        return response()->json(['ok' => false, 'msg' => 'Ya usaste todos los descartes disponibles']);
    }

    $session->descarte_usados++;
    $session->save();

    return response()->json(['ok' => true, 'msg' => 'Descarte usado', 'total_usados' => $session->descarte_usados]);
}

public function resetearDescarte($id)
{
    $session = GameSession::findOrFail($id);

    $session->descarte_usados = 0;
    $session->save();

    return response()->json(['ok' => true, 'msg' => 'Descartes reseteados']);
}
*/

}

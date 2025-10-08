<?php

namespace App\Services;

use App\Models\ParticipantAnswer;
use App\Models\ParticipantSession;
use App\Models\GameSession;
use App\Events\GameBonusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GamePointsService
{
    /**
     * Calcula el puntaje de un participante del público en LOTE
     * @param array $participantIds
     * @param int|null $gameSessionId
     * @return array [participant_id => total]
     */
    public static function calcularPuntajesParticipantes(array $participantIds, $gameSessionId = null): array
    {
        if (empty($participantIds)) return [];

        // Subquery para obtener la última respuesta por participante y pregunta
        $sub = DB::table('participant_answers')
            ->select(DB::raw('MAX(id) as id'))
            ->whereIn('participant_session_id', $participantIds)
            ->groupBy('participant_session_id', 'question_id');

        $rows = DB::table('participant_answers as pa')
            ->joinSub($sub, 'last', function($join) {
                $join->on('pa.id', '=', 'last.id');
            })
            ->select('pa.participant_session_id', 'pa.question_id', 'pa.option_label', 'pa.label_correcto')
            ->get();

        $result = array_fill_keys($participantIds, 0);
        foreach ($rows as $r) {
            $pid = $r->participant_session_id;
            if ($r->option_label && $r->label_correcto && $r->option_label === $r->label_correcto) {
                $result[$pid] += 1;
            }
        }

        return $result;
    }

    /**
     * Calcula el puntaje del invitado
     */


public static function calcularPuntajeInvitado(
    $gameSessionId,
    $selectedOption,
    $questionId,
    $labelCorrecto,
    $esOro = false,
    $apuestaX2 = false,
    $bonoEspecial = null
) {
    Log::info("[PUNTAJE INVITADO] game_session_id={$gameSessionId}, question_id={$questionId}, opcion={$selectedOption}, correcta={$labelCorrecto}");

    $session = GameSession::find($gameSessionId);
    if (!$session) {
        Log::warning("[PUNTAJE INVITADO] No existe la sesión {$gameSessionId}");
        return 0;
    }

    $selectedOptionNorm = strtoupper(trim((string)$selectedOption));
    $labelCorrectoNorm  = strtoupper(trim((string)$labelCorrecto));

    if ($esOro) return 5;

    if ($bonoEspecial === 'confio') {
        $tendencia = self::calcularTendencia($gameSessionId, $questionId);
        $trendOptionNorm = strtoupper(trim($tendencia['option'] ?? ''));
        return ($trendOptionNorm === $labelCorrectoNorm) ? 3 : 0;
    }

    if ($bonoEspecial === 'ahora_yo') {
        return ($selectedOptionNorm === $labelCorrectoNorm) ? 3 : 0;
    }

    $tendencia = self::calcularTendencia($gameSessionId, $questionId);
    $trendOptionNorm = strtoupper(trim($tendencia['option'] ?? ''));

    $invitadoAcierta = ($selectedOptionNorm === $labelCorrectoNorm);
    $publicoAcierta  = ($trendOptionNorm !== '' && $trendOptionNorm === $labelCorrectoNorm);

    $delta = 0;
    if ($invitadoAcierta && $publicoAcierta) $delta = 2;
    elseif ($invitadoAcierta && !$publicoAcierta) $delta = 3;
    elseif (!$invitadoAcierta && $publicoAcierta) $delta = -2;
    else $delta = -1;

    // ✅ Aplicar apuesta x2 ANTES de calcular
    if (!$esOro && $session->apuesta_x2_active) {
        Log::info("[APUESTA x2] Intentando aplicar apuesta. Usadas: {$session->apuesta_x2_usadas}");
        
        $consumida = $session->consumirApuesta();

        if ($consumida) {
            $delta *= 2;
            Log::info("[APUESTA x2] ✅ Aplicada exitosamente. Delta duplicado a: {$delta}");
            
            // ✅ Refrescar la instancia actual para tener valores actualizados
            $session = GameSession::find($gameSessionId);
            
            // ✅ Broadcast manual aquí porque consumirApuesta() no lo hace
            broadcast(new GameBonusUpdated($session));
        } else {
            Log::warning("[APUESTA x2] ❌ No se pudo aplicar (límite alcanzado o desactivada)");
        }
    }

    Log::info("[PUNTAJE INVITADO] Delta final calculado: {$delta}");

    return $delta;
}
    /**
     * Calcula la tendencia de una pregunta
     */
    public static function calcularTendencia($gameSessionId, $questionId)
    {
        $votes = ParticipantAnswer::whereHas('participantSession', function($q) use ($gameSessionId) {
                $q->where('game_session_id', $gameSessionId);
            })
            ->where('question_id', $questionId)
            ->select('option_label', DB::raw('count(*) as total'))
            ->groupBy('option_label')
            ->get();

        if ($votes->isEmpty()) return ['option' => null, 'votes' => 0, 'total_participants' => 0];

        $max = $votes->max('total');
        $candidates = $votes->where('total', $max);

        if ($candidates->count() > 1) return ['option' => null, 'votes' => $max, 'total_participants' => $votes->sum('total')];

        $winner = $candidates->first();
        return [
            'option' => $winner->option_label,
            'votes' => $winner->total,
            'total_participants' => $votes->sum('total'),
        ];
    }

    /**
     * Calcula tendencias de múltiples preguntas en 1 query
     */
    public static function calcularTendenciasMultiple($gameSessionId, array $questionIds): array
    {
        if (empty($questionIds)) return [];

        $votes = ParticipantAnswer::whereHas('participantSession', function($q) use ($gameSessionId) {
                $q->where('game_session_id', $gameSessionId);
            })
            ->whereIn('question_id', $questionIds)
            ->select('question_id', 'option_label', DB::raw('count(*) as total'))
            ->groupBy('question_id', 'option_label')
            ->get();

        $byQuestion = [];
        foreach ($votes as $v) {
            $byQuestion[$v->question_id][$v->option_label] = (int)$v->total;
        }

        $result = [];
        foreach ($questionIds as $qid) {
            $opts = $byQuestion[$qid] ?? [];
            if (empty($opts)) {
                $result[$qid] = ['option' => null, 'votes' => 0, 'total_participants' => 0];
                continue;
            }
            $total = array_sum($opts);
            $max = max($opts);
            $candidates = array_filter($opts, fn($t) => $t === $max);
            if (count($candidates) > 1) {
                $result[$qid] = ['option' => null, 'votes' => $max, 'total_participants' => $total];
            } else {
                $winner = array_keys($opts, $max)[0];
                $result[$qid] = ['option' => $winner, 'votes' => $max, 'total_participants' => $total];
            }
        }

        return $result;
    }

    /**
     * Verifica la racha del público usando las tendencias en bloque
     */
    public static function verificarTendenciaPublico($gameSessionId)
    {
        $preguntas = ParticipantAnswer::whereHas('participantSession', function($q) use ($gameSessionId) {
                $q->where('game_session_id', $gameSessionId);
            })
            ->distinct()
            ->orderBy('question_id')
            ->pluck('question_id')
            ->toArray();

        if (empty($preguntas)) return ['alcanzada' => false, 'correctas_consecutivas' => 0, 'racha_actual' => 0];

        $labels = ParticipantAnswer::whereIn('question_id', $preguntas)
            ->whereNotNull('label_correcto')
            ->select('question_id', DB::raw('MAX(label_correcto) as label'))
            ->groupBy('question_id')
            ->pluck('label', 'question_id')
            ->toArray();

        $tendencias = self::calcularTendenciasMultiple($gameSessionId, $preguntas);

        $rachaActual = 0;
        $maxConsecutivas = 0;

        foreach ($preguntas as $qid) {
            $label = $labels[$qid] ?? null;
            if (!$label) { $rachaActual = 0; continue; }

            $t = $tendencias[$qid] ?? ['option' => null];
            $publicoAcierta = ($t['option'] !== null && $t['option'] === $label);

            if ($publicoAcierta) {
                $rachaActual++;
                $maxConsecutivas = max($maxConsecutivas, $rachaActual);
            } else {
                $rachaActual = 0;
            }
        }

        return [
            'alcanzada' => $maxConsecutivas >= 10,
            'correctas_consecutivas' => $maxConsecutivas,
            'racha_actual' => $rachaActual,
        ];
    }

    /**
     * Verifica victoria invitado
     */
    public static function verificarVictoriaInvitado($gameSessionId)
    {
        $session = GameSession::find($gameSessionId);
        if (!$session) return ['gano' => false, 'puntaje_actual' => 0, 'objetivo' => 25];

        $objetivo = ($session->modo_juego === 'express') ? 10 : 25;
        $puntajeActual = $session->guest_points ?? 0;

        return ['gano' => $puntajeActual >= $objetivo, 'puntaje_actual' => $puntajeActual, 'objetivo' => $objetivo];
    }

    /**
 * Calcula el puntaje total del público basado en tendencias
 */
public static function calcularPuntajePublico($gameSessionId): int
{
    $preguntas = ParticipantAnswer::whereHas('participantSession', function($q) use ($gameSessionId) {
            $q->where('game_session_id', $gameSessionId);
        })
        ->distinct()
        ->orderBy('question_id')
        ->pluck('question_id')
        ->toArray();

    if (empty($preguntas)) return 0;

    $labels = ParticipantAnswer::whereIn('question_id', $preguntas)
        ->whereNotNull('label_correcto')
        ->select('question_id', DB::raw('MAX(label_correcto) as label'))
        ->groupBy('question_id')
        ->pluck('label', 'question_id')
        ->toArray();

    $tendencias = self::calcularTendenciasMultiple($gameSessionId, $preguntas);

    $puntaje = 0;

    foreach ($preguntas as $qid) {
        $label = $labels[$qid] ?? null;
        if (!$label) continue;

        $t = $tendencias[$qid] ?? ['option' => null];
        $publicoAcierta = ($t['option'] !== null && $t['option'] === $label);

        if ($publicoAcierta) {
            $puntaje++; // +1 por cada pregunta donde la tendencia acierta
        }
    }

    return $puntaje;
}
}

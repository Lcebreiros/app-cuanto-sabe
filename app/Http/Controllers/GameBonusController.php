<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameSession;
use App\Events\GameBonusUpdated;
use Illuminate\Support\Facades\Log;

class GameBonusController extends Controller
{
    public function toggleApuestaX2(Request $request) {
    $session = GameSession::where('status', 'active')->latest()->first();
    if (!$session) {
        return response()->json(['error' => 'No hay sesión activa.'], 404);
    }

    $limite = ($session->modo_juego === 'express') ? 1 : 2;

    if ($session->apuesta_x2_active) {
        $session->apuesta_x2_active = false;
        Log::info("[BONUS] Apuesta x2 DESACTIVADA");
    } else {
        if ($session->apuesta_x2_usadas >= $limite) {
            return response()->json([
                'error' => 'Ya usaste todas las apuestas x2 disponibles',
                'success' => false,
                'apuesta_x2_active' => false
            ], 400);
        }
        $session->apuesta_x2_active = true;
        Log::info("[BONUS] Apuesta x2 ACTIVADA");
    }

    $session->save();
    
    Log::info("[BONUS] Broadcasting evento GameBonusUpdated", [
        'apuesta_x2_active' => $session->apuesta_x2_active,
        'apuesta_x2_usadas' => $session->apuesta_x2_usadas
    ]);
    
    broadcast(new GameBonusUpdated($session));

    return response()->json([
        'success' => true,
        'apuesta_x2_active' => $session->apuesta_x2_active,
        'apuesta_x2_usadas' => $session->apuesta_x2_usadas,
        'apuesta_x2_disponibles' => $limite - $session->apuesta_x2_usadas
    ]);
}

    public function toggleDescarte(Request $request) {
        $session = GameSession::where('status', 'active')->latest()->first();
        if (!$session) {
            return response()->json(['error' => 'No hay sesión activa.'], 404);
        }

        // El descarte es 1 vez en ambos modos
        if ($session->descarte_usados >= 1) {
            return response()->json([
                'error' => 'Ya usaste el descarte disponible',
                'success' => false,
                'descarte_usado' => true
            ], 400);
        }

        // Usar descarte
        $session->descarte_usados = 1;
        $session->save();

        broadcast(new GameBonusUpdated($session));

        return response()->json([
            'success' => true,
            'descarte_usados' => $session->descarte_usados,
            'descarte_disponible' => false
        ]);
    }
}
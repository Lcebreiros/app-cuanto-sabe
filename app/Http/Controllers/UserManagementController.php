<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Mostrar la lista de usuarios
     */
    public function index(): View
    {
        // Obtener todos los usuarios ordenados por fecha de creación
        $users = User::orderBy('created_at', 'desc')->get();
        
return view('users', compact('users'));    }

    /**
     * Actualizar el rol de un usuario
     */
public function updateRole(Request $request, $userId): JsonResponse
{
    $request->validate([
        'role' => 'required|in:user,moderator,admin'
    ]);

    $user = User::find($userId);

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ], 404);
    }

    // Verificar que no sea el usuario actual
    if ($user->id === auth()->id()) {
        return response()->json([
            'success' => false,
            'message' => 'No puedes cambiar tu propio rol'
        ], 403);
    }

    // Verificar permisos (solo admins pueden cambiar roles)
    if (auth()->user()->role !== 'admin') {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permisos para realizar esta acción'
        ], 403);
    }

    $user->role = $request->role;
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Rol actualizado correctamente'
    ]);
}


    /**
     * Eliminar un usuario
     */
    public function destroy(User $user): JsonResponse
    {
        // Verificar que no sea el usuario actual
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar tu propia cuenta'
            ], 403);
        }

        // Verificar permisos (solo admins pueden eliminar usuarios)
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado correctamente'
        ]);
    }
}
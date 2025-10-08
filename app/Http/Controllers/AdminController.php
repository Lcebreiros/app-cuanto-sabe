<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role !== 'admin') {
            abort(403, 'No autorizado');
        }

        return view('admin');
    }

    // Mostrar formulario creación usuario
    public function createUser()
    {
        $user = auth()->user();

        if ($user->role !== 'admin') {
            abort(403, 'No autorizado');
        }

        return view('admin.create-user');
    }

    // Guardar usuario nuevo
    public function storeUser(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'admin') {
            abort(403, 'No autorizado');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,user',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.create-user')->with('success', 'Usuario creado correctamente');
    }
}

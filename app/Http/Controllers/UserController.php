<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Método para obtener todos los usuarios
    public function index(Request $request)
    {
        // Verificar si el usuario está autenticado y es admin
        if (!Auth::check() || !in_array(Auth::user()->email, config('admins.emails'))) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Obtener todos los usuarios
        $users = User::all();

        return response()->json($users);
    }

    // Método para dar de alta a un usuario
    public function alta($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = true; // Cambia el estado a activo
        $user->save();

        return response()->json(['message' => 'Usuario dado de alta correctamente.']);
    }

    // Método para dar de baja (soft delete) a un usuario
    public function baja($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = false; // Cambia el estado a inactivo
        $user->save();

        return response()->json(['message' => 'Usuario dado de baja correctamente.']);
    }

    // Método para modificar un usuario
    public function modificar(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->username = $request->input('username'); // Suponiendo que quieres modificar el nombre de usuario
        // Agrega más campos según sea necesario
        $user->save();

        return response()->json(['message' => 'Usuario modificado correctamente.']);
    }
} 
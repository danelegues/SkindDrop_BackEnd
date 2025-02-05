<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'member_since' => $user->created_at,
                'email_verified_at' => $user->email_verified_at,
                'balance' => $user->balance,
                // Agrega cualquier otro campo que necesites
            ]
        ]);
    }
    public function updateBalance(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric'
        ]);

        $user = Auth::user();
        $newBalance = $user->balance + $request->amount;
        
        // Verificar que el balance no sea negativo
        if ($newBalance < 0) {
            return response()->json([
                'message' => 'Fondos insuficientes'
            ], 422);
        }

        $user->balance = $newBalance;
        $user->save();

        return response()->json([
            'message' => 'Balance actualizado correctamente',
            'balance' => $user->balance
        ]);
    }

    public function store(Request $request)
    {
    $request->validate([
        'balance' => 'required|numeric|min:0',
    ]);

    // Aquí puedes crear el usuario
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'balance' => $request->balance,
        'password' => bcrypt($request->password),
    ]);

    return response()->json($user);
}

    public function update(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $request->user()->id,
        ]);

        try {
            $user = $request->user();
            $user->username = $request->username;
            $user->save();

            return response()->json([
                'message' => 'Perfil actualizado correctamente',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'member_since' => $user->created_at,
                    'email_verified_at' => $user->email_verified_at,
                    'balance' => $user->balance,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => ['required', function ($attribute, $value, $fail) {
                    if (!Hash::check($value, Auth::user()->password)) {
                        $fail('La contraseña actual es incorrecta.');
                    }
                }],
                'password' => ['required', 'confirmed', Password::min(8)
                    ->mixedCase()   // Requiere mayúsculas y minúsculas
                    ->numbers()     // Requiere números
                ],
            ]);

            $user = $request->user();
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'message' => 'Contraseña actualizada correctamente'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la contraseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
} 
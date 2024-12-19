<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
                // Agrega cualquier otro campo que necesites
            ]
        ]);
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
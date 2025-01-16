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
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01|max:10000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            
            // Si es una resta (compra de caja), verificar que haya suficiente balance
            if ($request->has('subtract') && $request->subtract) {
                if ($user->balance < $request->amount) {
                    return response()->json([
                        'message' => 'Balance insuficiente'
                    ], 400);
                }
                $newBalance = $user->balance - $request->amount;
            } else {
                // Si es una suma (recarga de saldo)
                $newBalance = $user->balance + $request->amount;
            }

            \DB::beginTransaction();
            try {
                $user->balance = $newBalance;
                $user->save();

                // Crear la transacción
                $user->transactions()->create([
                    'type' => $request->has('subtract') ? 'buy' : 'deposit',
                    'amount' => $request->amount,
                    'price' => $request->amount,
                    'status' => 'completed'
                ]);

                \DB::commit();

                return response()->json([
                    'message' => 'Balance actualizado correctamente',
                    'balance' => $user->balance
                ]);

            } catch (\Exception $e) {
                \DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error en updateBalance: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el balance',
                'error' => $e->getMessage()
            ], 500);
        }
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
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;

Route::get('/test', function () {
    return response()->json([
        'message' => 'Conexión exitosa con la API',
        'status' => 'OK'
    ]);
});

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Rutas que requieren verificación de email
    Route::get('/protected-route', function () {
        return response()->json(['message' => 'Esta ruta requiere verificación']);
    });

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile/update', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
}); 
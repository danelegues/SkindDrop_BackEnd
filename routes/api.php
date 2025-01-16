<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CrateController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MarketListingController;


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
    Route::put('/profile/balance', [ProfileController::class, 'updateBalance']);
}); 

Route::middleware('auth:sanctum')->group(function () {
    // Rutas de inventario
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/inventory', [InventoryController::class, 'store']); // Esta es la nueva ruta para la apertura de cajas
    Route::get('/market', [MarketListingController::class, 'index']);
});

Route::get('/items', [ItemController::class, 'index']);
Route::get('/crates', [CrateController::class, 'index']);
Route::post('/crates', [CrateController::class, 'store']);
Route::delete('/crates/{id}', [CrateController::class, 'destroy']);

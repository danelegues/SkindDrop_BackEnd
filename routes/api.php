<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CrateController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MarketListingController;
use App\Http\Controllers\UserController;



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
    Route::post('/inventory', [InventoryController::class, 'store']); 
    Route::post('/inventory/add-item', [InventoryController::class, 'addItem']);
    Route::get('/market', [MarketListingController::class, 'index']);
    
    // Rutas del mercado
    Route::get('/market/items', [MarketListingController::class, 'index']);
    Route::post('/market/items', [MarketListingController::class, 'store']);
    Route::delete('/market/remove/{id}', [MarketListingController::class, 'destroy']);
    Route::post('crates/open', [CrateController::class, 'openCrate']);
});

Route::get('/items', [ItemController::class, 'index']);
Route::get('/crates', [CrateController::class, 'index']);
Route::post('/crates', [CrateController::class, 'store']);
Route::delete('/crates/{id}', [CrateController::class, 'destroy']);

Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'index']);

// Rutas para usuarios
Route::middleware('auth:sanctum')->post('/users/{id}/alta', [UserController::class, 'alta']);
Route::middleware('auth:sanctum')->post('/users/{id}/baja', [UserController::class, 'baja']);
Route::middleware('auth:sanctum')->put('/users/{id}/modificar', [UserController::class, 'modificar']);

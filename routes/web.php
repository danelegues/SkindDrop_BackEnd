<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/verify-email', function (Request $request) {
    try {
        \Log::info('Iniciando verificación de email', [
            'request_id' => $request->query('id'),
            'request_hash' => $request->query('hash'),
            'request_all' => $request->all()
        ]);

        $user = User::find($request->query('id'));
        
        if (!$user) {
            \Log::error('Usuario no encontrado', ['id' => $request->query('id')]);
            throw new \Exception('Usuario no encontrado');
        }

        \Log::info('Usuario encontrado', [
            'user_email' => $user->email,
            'user_verified' => $user->hasVerifiedEmail()
        ]);

        if (!hash_equals(sha1($user->email), $request->query('hash'))) {
            \Log::error('Hash no coincide', [
                'generated_hash' => sha1($user->email),
                'received_hash' => $request->query('hash')
            ]);
            throw new \Exception('URL de verificación inválida');
        }

        if ($user->hasVerifiedEmail()) {
            \Log::info('Usuario ya verificado');
            return redirect('http://3.89.136.123:8000/login?status=already-verified');
        }

        if ($user->markEmailAsVerified()) {
            \Log::info('Email verificado exitosamente');
            event(new Verified($user));
        }

        return redirect('http://3.89.136.123:8000/login?status=verified');

    } catch (\Exception $e) {
        \Log::error('Error en verificación de email: ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);
        return redirect('http://3.89.136.123:8000/login?status=error');
    }
})->name('verification.verify');

Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    try {
        // Validación básica de parámetros
        if (!$id || !$hash) {
            throw new \Exception('Parámetros inválidos');
        }

        $user = User::findOrFail($id);
        
        // Verificar el hash
        if (!hash_equals((string)$hash, sha1($user->email))) {
            throw new \Exception('Hash inválido');
        }

        // Si ya está verificado
        if ($user->hasVerifiedEmail()) {
            return redirect('http://3.89.136.123:8000/login?status=already-verified');
        }

        // Marcar como verificado
        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect('http://3.89.136.123:8000/login?status=verified');

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return redirect('http://3.89.136.123:8000/login?status=user-not-found');
    } catch (\Exception $e) {
        \Log::error('Error de verificación: ' . $e->getMessage());
        return redirect('http://3.89.136.123:8000/login?status=verification-failed');
    }
})->middleware('web')->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Link de verificación enviado!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::post('/login', [LoginController::class, 'login'])->name('login');

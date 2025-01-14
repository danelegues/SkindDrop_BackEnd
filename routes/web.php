<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Request $request) {
    $user = User::find($request->id);
    $user->email_verified_at = now();
    $user->save();
    return view('auth.verification-success');
})->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Link de verificación enviado!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::post('/login', [LoginController::class, 'login'])->name('login');

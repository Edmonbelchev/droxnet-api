<?php

use App\Http\Controllers\API\EmailValidateController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;

Route::post('/login', [AuthController::class, 'login'])
    ->name('login');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register');


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // Profile routes
    Route::resource('profile', ProfileController::class)
        ->name('index', 'profile.index')
        ->name('update', 'profile.update')
        ->name('delete', 'profile.destroy');

    // Email validation routes
    Route::post('/email/validate', [EmailValidateController::class, 'validateEmail'])
        ->name('email.validate');
    
    Route::post('/email/generate-token', [EmailValidateController::class, 'generateToken'])
        ->name('email.generate-token');
});

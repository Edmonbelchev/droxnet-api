<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\ProfileResource;
use App\Http\Controllers\API\AuthController;

Route::post('/login', [AuthController::class, 'login'])
    ->name('login');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register');
    
Route::get('/user', function (Request $request) {
    return ProfileResource::make($request->user());
})->middleware('auth:sanctum');

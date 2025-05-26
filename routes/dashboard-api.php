<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\TransactionsController;
use App\Http\Controllers\Dashboard\UsersController;
use App\Http\Controllers\Dashboard\JobsController;
use App\Http\Controllers\Dashboard\StripeController;

Route::middleware(['api'])->group(function () {
    Route::get('/transactions-list', [TransactionsController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionsController::class, 'show']);

    Route::get('/users-list', [UsersController::class, 'index']);
    Route::get('/user/{uuid}', [UsersController::class, 'show']);

    Route::get('/jobs-list', [JobsController::class, 'index']);
    Route::get('/job/{id}', [JobsController::class, 'show']);

    // Stripe routes
    Route::get('/stripe/balance', [StripeController::class, 'getBalance']);
    Route::get('/stripe/balance/history', [StripeController::class, 'getBalanceHistory']);
    Route::get('/stripe/transaction', [StripeController::class, 'getTransactionDetails']);
});

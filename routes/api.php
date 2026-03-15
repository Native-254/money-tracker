<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Money Tracker — API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api (handled by Laravel's RouteServiceProvider).
|
| Route summary:
|
|   POST   /api/register                           → Register a new user
|   POST   /api/login                              → Login and receive token
|
|   POST   /api/logout                             → Logout (invalidate token)
|   GET    /api/users/{user}                       → View user profile (wallets + total balance)
|   POST   /api/users/{user}/wallets               → Create a wallet for a user
|   GET    /api/wallets/{wallet}                   → View a single wallet (balance + transactions)
|   POST   /api/wallets/{wallet}/transactions      → Add a transaction to a wallet
|
*/

// ── Public routes (no token required) ────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login'])->middleware('throttle:login');

// ── Protected routes (token required) ────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // User
    Route::get('/users/{user}', [UserController::class, 'show']);

    // Wallet
    Route::post('/users/{user}/wallets',         [WalletController::class, 'store']);
    Route::get('/wallets/{wallet}',              [WalletController::class, 'show']);
    Route::post('/wallets/{wallet}/invite',      [WalletController::class, 'invite']); // ← ADD THIS

    // Transactions
    Route::post('/wallets/{wallet}/transactions', [TransactionController::class, 'store']);

});

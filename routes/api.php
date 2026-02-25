<?php

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
| No authentication middleware is applied as per the assessment requirements.
|
| Route summary:
|
|   POST   /api/users                              → Create a user
|   GET    /api/users/{user}                       → View user profile (wallets + total balance)
|   POST   /api/users/{user}/wallets               → Create a wallet for a user
|   GET    /api/wallets/{wallet}                   → View a single wallet (balance + transactions)
|   POST   /api/wallets/{wallet}/transactions      → Add a transaction to a wallet
|
*/

// ── User routes ──────────────────────────────────────────────────────────────
Route::post('/users',        [UserController::class, 'store']);   // Create user
Route::get('/users/{user}',  [UserController::class, 'show']);    // View user profile

// ── Wallet routes ─────────────────────────────────────────────────────────────
Route::post('/users/{user}/wallets', [WalletController::class, 'store']);  // Create wallet
Route::get('/wallets/{wallet}',      [WalletController::class, 'show']);   // View wallet

// ── Transaction routes ────────────────────────────────────────────────────────
Route::post('/wallets/{wallet}/transactions', [TransactionController::class, 'store']); // Add transaction

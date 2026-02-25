<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * WalletController
 *
 * Handles creating wallets for a user and retrieving a single wallet's details.
 */
class WalletController extends Controller
{
    /**
     * POST /api/users/{user}/wallets
     *
     * Create a new wallet for the specified user.
     *
     * Required fields:
     *   - name (string) — a friendly label for the wallet, e.g. "Business A"
     *
     * @param  Request  $request
     * @param  User     $user     (route-model bound)
     * @return JsonResponse
     */
    public function store(Request $request, User $user): JsonResponse
    {
        // Validate the wallet name
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Associate the new wallet with the given user
        $wallet = $user->wallets()->create($validated);

        return response()->json([
            'message' => 'Wallet created successfully.',
            'data'    => [
                'id'      => $wallet->id,
                'user_id' => $wallet->user_id,
                'name'    => $wallet->name,
                'balance' => 0.00,   // new wallet always starts at zero
            ],
        ], 201);
    }

    /**
     * GET /api/wallets/{wallet}
     *
     * Retrieve a single wallet's details.
     * Returns the wallet's balance and its full transaction history.
     *
     * @param  Wallet  $wallet   (route-model bound)
     * @return JsonResponse
     */
    public function show(Wallet $wallet): JsonResponse
    {
        // Eager-load transactions, newest first
        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => [
                'id'           => $wallet->id,
                'name'         => $wallet->name,
                'user_id'      => $wallet->user_id,
                'balance'      => $wallet->balance(),
                'transactions' => $transactions,
            ],
        ]);
    }
}

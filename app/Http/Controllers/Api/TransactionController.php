<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * TransactionController
 *
 * Handles adding financial transactions (income or expense) to a wallet.
 */
class TransactionController extends Controller
{
    /**
     * POST /api/wallets/{wallet}/transactions
     *
     * Add a new transaction to the specified wallet.
     *
     * Required fields:
     *   - type        : 'income' or 'expense'
     *   - amount      : positive number (e.g. 150.00)
     *
     * Optional fields:
     *   - description : a short note about the transaction
     *
     * Balance effects:
     *   - income  → increases the wallet balance
     *   - expense → decreases the wallet balance
     *
     * @param  Request  $request
     * @param  Wallet   $wallet   (route-model bound)
     * @return JsonResponse
     */
    public function store(Request $request, Wallet $wallet): JsonResponse
    {
        // Validate all incoming fields
        $validated = $request->validate([
            'type'        => 'required|in:income,expense',   // only these two values are allowed
            'amount'      => 'required|numeric|gt:0',        // must be a positive number
            'description' => 'nullable|string|max:500',
        ]);

        // Create the transaction linked to this wallet
        $transaction = $wallet->transactions()->create($validated);

        // Return the new transaction along with the wallet's updated balance
        return response()->json([
            'message'        => 'Transaction recorded successfully.',
            'data'           => $transaction,
            'wallet_balance' => $wallet->balance(),
        ], 201);
    }
}

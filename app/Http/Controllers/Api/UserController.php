<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * UserController
 *
 * Handles creating new user accounts and retrieving a user's full profile.
 */
class UserController extends Controller
{
    /**
     * POST /api/users
     *
     * Create a new user account.
     *
     * Required fields:
     *   - name  (string)
     *   - email (string, valid email, unique)
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the incoming request fields
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        // Create the user record
        $user = User::create($validated);

        return response()->json([
            'message' => 'User account created successfully.',
            'data'    => $user,
        ], 201);
    }

    /**
     * GET /api/users/{user}
     *
     * Retrieve a user's profile.
     * Returns all their wallets, each wallet's balance,
     * and the total balance across all wallets.
     *
     * @param  User  $user   (route-model bound)
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        // Load all wallets for this user (no transactions here — kept lightweight)
        $wallets = $user->wallets()->get();

        // Build the wallet list and attach a calculated balance to each
        $walletData = $wallets->map(function (Wallet $wallet) {
            return [
                'id'      => $wallet->id,
                'name'    => $wallet->name,
                'balance' => $wallet->balance(),
            ];
        });

        return response()->json([
            'data' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'wallets'       => $walletData,
                'total_balance' => $user->totalBalance(),
            ],
        ]);
    }
}

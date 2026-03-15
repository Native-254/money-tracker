<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * WalletController
 *
 * Handles creating wallets for a user and retrieving a single wallet's details.
 */
class WalletController extends Controller
{
    /**
     * POST /api/users/{user}/wallets
     */
    public function store(Request $request, User $user): JsonResponse
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        if ($user->id !== $authUser->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $wallet = $user->wallets()->create($validated);

        $wallet->members()->attach($authUser->id, ['role' => 'owner']);

        return response()->json([
            'message' => 'Wallet created successfully.',
            'data'    => [
                'id'      => $wallet->id,
                'user_id' => $wallet->user_id,
                'name'    => $wallet->name,
                'balance' => 0.00,
            ],
        ], 201);
    }

    /**
     * GET /api/wallets/{wallet}
     */
    public function show(Wallet $wallet): JsonResponse
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        $isMember = $wallet->members()
            ->where('user_id', $authUser->id)
            ->exists();

        if (! $isMember) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

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

    /**
     * POST /api/wallets/{wallet}/invite
     */
    public function invite(Request $request, Wallet $wallet): JsonResponse
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        $isOwner = $wallet->members()
            ->where('user_id', $authUser->id)
            ->where('role', 'owner')
            ->exists();

        if (! $isOwner) {
            return response()->json(['message' => 'Only the wallet owner can invite members.'], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $invitee = User::where('email', $validated['email'])->first();

        if ($wallet->members()->where('user_id', $invitee->id)->exists()) {
            return response()->json(['message' => 'User is already a member of this wallet.'], 409);
        }

        $wallet->members()->attach($invitee->id, ['role' => 'member']);

        return response()->json(['message' => 'User added to wallet successfully.']);
    }
}

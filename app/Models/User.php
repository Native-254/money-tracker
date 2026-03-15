<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 *
 * Represents a registered user of the Money Tracker system.
 * A user can own one or more wallets.
 */
class User extends Model
{
    use HasApiTokens;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    /**
     * All wallets belonging to this user (owned wallets).
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    /**
     * All wallets this user is a member of (shared wallets).
     * Uses the wallet_user pivot table with role and timestamps.
     */
    public function sharedWallets(): BelongsToMany
{
    return $this->belongsToMany(Wallet::class, 'wallet_user')
                ->withPivot('role')
                ->withTimestamps();
}

    /**
     * The overall balance across every wallet this user owns.
     * Calculated by summing each wallet's individual balance.
     *
     * @return float
     */
    public function totalBalance(): float
    {
        return (float) $this->wallets()->with('transactions')->get()
            ->sum(fn(Wallet $wallet) => $wallet->balance());
    }
    }

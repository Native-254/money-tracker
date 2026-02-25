<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * User Model
 *
 * Represents a registered user of the Money Tracker system.
 * A user can own one or more wallets.
 */
class User extends Model
{
    protected $fillable = ['name', 'email'];

    /**
     * All wallets belonging to this user.
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
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

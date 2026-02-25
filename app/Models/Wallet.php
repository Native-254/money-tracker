<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Wallet Model
 *
 * Represents a named account (wallet) belonging to a user.
 * Balance is derived dynamically from its transactions.
 */
class Wallet extends Model
{
    protected $fillable = ['user_id', 'name'];

    /**
     * The user who owns this wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All transactions recorded against this wallet.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Calculate the current balance for this wallet.
     *
     * Aggregates all income transactions and subtracts all expenses.
     * This is computed on the fly so the stored record never drifts out of sync.
     *
     * @return float
     */
    public function balance(): float
    {
        $income  = (float) $this->transactions()->where('type', 'income')->sum('amount');
        $expense = (float) $this->transactions()->where('type', 'expense')->sum('amount');

        return $income - $expense;
    }
}

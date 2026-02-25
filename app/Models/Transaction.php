<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Transaction Model
 *
 * Represents a single financial entry (income or expense) on a wallet.
 * The 'amount' is always a positive value; the 'type' determines its effect.
 */
class Transaction extends Model
{
    protected $fillable = ['wallet_id', 'type', 'amount', 'description'];

    /**
     * Ensure 'amount' is always returned as a float.
     */
    protected $casts = [
        'amount' => 'float',
    ];

    /**
     * The wallet this transaction belongs to.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}

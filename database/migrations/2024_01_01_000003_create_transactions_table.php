<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: transactions table
 *
 * Records every income or expense entry against a wallet.
 *
 * type  : 'income'  → adds to wallet balance
 *         'expense' → subtracts from wallet balance
 *
 * amount: always stored as a positive decimal.
 *         The sign is determined at query time by the 'type' column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')
                  ->constrained()
                  ->cascadeOnDelete();  // remove transactions when wallet is deleted
            $table->enum('type', ['income', 'expense']);
            $table->decimal('amount', 15, 2);   // e.g. 99999999999999.99
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

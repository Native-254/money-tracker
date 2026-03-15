<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('wallet_user', function (Blueprint $table) {
        $table->id();
        $table->foreignId('wallet_id')
              ->constrained()
              ->onDelete('cascade');
        $table->foreignId('user_id')
              ->constrained()
              ->onDelete('cascade');
        $table->enum('role', ['owner', 'member'])->default('member');
        $table->timestamps();

        $table->unique(['wallet_id', 'user_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_user');
    }
};

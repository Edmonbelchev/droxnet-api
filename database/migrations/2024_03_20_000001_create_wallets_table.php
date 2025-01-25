<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_uuid')->constrained('users', 'uuid')->onDelete('cascade');
            $table->decimal('balance', 10, 2)->default(0);
            $table->decimal('escrow_balance', 10, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_connect_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};

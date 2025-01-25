<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type'); // deposit, withdrawal, escrow_hold, escrow_release, refund
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('USD');
            $table->string('status'); // pending, completed, failed, disputed
            $table->string('stripe_payment_id')->nullable();
            $table->string('stripe_transfer_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('milestone_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('raised_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('raised_against')->constrained('users')->onDelete('cascade');
            $table->string('type'); // payment, quality, communication, other
            $table->string('status'); // open, under_review, resolved, closed
            $table->text('description');
            $table->text('resolution')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};

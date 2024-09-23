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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_uuid');
            $table->uuid('job_id');
            $table->string('subject', 128);
            $table->text('description', 512);
            $table->decimal('price', 10, 2);
            $table->enum('status', ['pending', 'accepted', 'rejected']);
            $table->integer('duration')->default(0)->nullable(false);
            $table->enum('duration_type', ['days', 'weeks', 'months'])->default('days')->nullable(false);
            $table->timestamps();

            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('job_id')->references('id')->on('jobs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};

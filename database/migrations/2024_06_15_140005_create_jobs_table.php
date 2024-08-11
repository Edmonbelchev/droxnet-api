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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_uuid');
            $table->string('title');
            $table->text('description');
            $table->string('level');
            $table->string('type');
            $table->string('budget');
            $table->string('duration');
            $table->string('location');
            $table->json('languages')->nullable();

            $table->timestamps();

            $table->foreign('user_uuid')
                  ->references('uuid')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};

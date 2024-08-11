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
        Schema::create('company_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_uuid');
            $table->string('company_name');
            $table->string('company_website')->nullable();
            $table->string('company_size')->nullable();
            $table->string('department')->nullable();
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
        Schema::dropIfExists('company_details');
    }
};

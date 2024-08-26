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
        Schema::table('jobs', function (Blueprint $table) {
            $table->boolean('show_attachments')->default(false)->after('languages');
            $table->string('country', 32)->nullable()->after('location');
            $table->enum('budget_type', ['hourly', 'fixed'])->default('fixed')->after('budget');
            $table->enum('status', ['proposal', 'ongoing', 'completed', 'cancelled'])->default('proposal')->after('show_attachments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('show_attachments');
            $table->dropColumn('country');
            $table->dropColumn('budget_type');
            $table->dropColumn('status');
        });
    }
};

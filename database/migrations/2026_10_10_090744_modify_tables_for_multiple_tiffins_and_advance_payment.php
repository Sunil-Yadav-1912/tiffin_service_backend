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
        // Pivot table for multiple subscriptions
        Schema::create('tiffin_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiffin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Add advance payment to users
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('advance_payment', 10, 2)->default(0)->after('selected_tiffin_id');
        });

        // Add advance_deducted and payable_amount to bills
        Schema::table('bills', function (Blueprint $table) {
            $table->decimal('advance_deducted', 10, 2)->default(0)->after('total_amount');
            $table->decimal('payable_amount', 10, 2)->default(0)->after('advance_deducted');
        });

        // Add tiffin_id to daily logs
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->foreignId('tiffin_id')->nullable()->constrained()->cascadeOnDelete()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->dropForeign(['tiffin_id']);
            $table->dropColumn('tiffin_id');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['advance_deducted', 'payable_amount']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('advance_payment');
        });

        Schema::dropIfExists('tiffin_user');
    }
};

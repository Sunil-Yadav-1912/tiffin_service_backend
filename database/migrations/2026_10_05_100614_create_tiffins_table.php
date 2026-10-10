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
        Schema::create('tiffins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 8, 2);
            $table->string('description');
            $table->text('long_description');
            $table->string('image')->nullable();
            $table->string('meal_type'); // 'Lunch', 'Dinner', 'Both'
            $table->string('diet_type'); // 'Veg', 'Non-Veg', 'Jain'
            $table->string('category'); // 'Daily', 'Premium', 'Diet', 'Special'
            $table->json('items_included');
            $table->boolean('is_available')->default(true);
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiffins');
    }
};

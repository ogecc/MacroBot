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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id', 'recipes_user_id_fk')->references('id')->on('users')->cascadeOnDelete();
            $table->index('user_id');
            $table->string('title', 150);
            $table->text('prompt');
            $table->string('meal_type', 20)->nullable();
            $table->unsignedSmallInteger('servings');
            $table->unsignedSmallInteger('prep_time_min');
            $table->unsignedSmallInteger('cook_time_min');
            $table->string('cuisine', 50)->nullable();
            $table->string('difficulty', 10);
            $table->unsignedSmallInteger('calories_per_serving');
            $table->decimal('protein_g', 5, 2);
            $table->decimal('carbs_g', 5, 2);
            $table->decimal('fat_g', 5, 2);
            $table->text('why_recommended')->nullable();
            $table->json('ingredients');
            $table->json('steps');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};

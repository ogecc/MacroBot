<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->unsignedTinyInteger('age');
            $table->string('gender', 10);
            $table->unsignedSmallInteger('height_cm');
            $table->decimal('weight_kg', 5, 2);
            $table->string('activity_level', 25);
            $table->string('goal', 10);
            $table->decimal('target_weight_kg', 5, 2)->nullable();
            $table->unsignedSmallInteger('daily_calorie_goal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};

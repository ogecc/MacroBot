<?php

namespace App\Models;

use Database\Factories\RecipeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipe extends Model
{
    /** @use HasFactory<RecipeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'prompt',
        'meal_type',
        'servings',
        'prep_time_min',
        'cook_time_min',
        'cuisine',
        'difficulty',
        'calories_per_serving',
        'protein_g',
        'carbs_g',
        'fat_g',
        'why_recommended',
        'ingredients',
        'steps',
    ];

    protected function casts(): array
    {
        return [
            'ingredients' => 'array',
            'steps' => 'array',
            'calories_per_serving' => 'integer',
            'servings' => 'integer',
            'prep_time_min' => 'integer',
            'cook_time_min' => 'integer',
            'protein_g' => 'float',
            'carbs_g' => 'float',
            'fat_g' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

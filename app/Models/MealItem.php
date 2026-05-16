<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealItem extends Model
{
    /** @use HasFactory<\Database\Factories\MealItemFactory> */
    use HasFactory;

    protected $fillable = ['meal_id', 'name', 'quantity', 'unit', 'calories', 'protein_g', 'carbs_g', 'fat_g'];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'protein_g' => 'decimal:2',
            'carbs_g' => 'decimal:2',
            'fat_g' => 'decimal:2',
        ];
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }
}

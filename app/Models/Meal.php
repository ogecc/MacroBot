<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meal extends Model
{
    /** @use HasFactory<\Database\Factories\MealFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'eaten_at', 'image_path', 'notes'];

    protected function casts(): array
    {
        return [
            'eaten_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MealItem::class);
    }

    public function getTotalCaloriesAttribute(): int
    {
        return (int) $this->items->sum('calories');
    }

    public function getTotalProteinGAttribute(): float
    {
        return (float) $this->items->sum('protein_g');
    }

    public function getTotalCarbsGAttribute(): float
    {
        return (float) $this->items->sum('carbs_g');
    }

    public function getTotalFatGAttribute(): float
    {
        return (float) $this->items->sum('fat_g');
    }
}

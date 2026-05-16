<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\Goal;
use Database\Factories\UserProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    /** @use HasFactory<UserProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'age',
        'gender',
        'height_cm',
        'weight_kg',
        'goal',
        'goal_adjustment',
        'target_weight_kg',
        'daily_calorie_goal',
        'locale',
    ];

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'goal' => Goal::class,
            'weight_kg' => 'decimal:2',
            'target_weight_kg' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

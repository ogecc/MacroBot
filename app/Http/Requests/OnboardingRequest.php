<?php

namespace App\Http\Requests;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\Goal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'age' => ['required', 'integer', 'min:13', 'max:120'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'height_cm' => ['required', 'integer', 'min:100', 'max:250'],
            'weight_kg' => ['required', 'numeric', 'min:30', 'max:300'],
            'activity_level' => ['required', Rule::enum(ActivityLevel::class)],
            'goal' => ['required', Rule::enum(Goal::class)],
            'target_weight_kg' => ['nullable', 'numeric', 'min:30', 'max:300'],
        ];
    }
}

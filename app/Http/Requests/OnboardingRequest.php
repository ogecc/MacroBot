<?php

namespace App\Http\Requests;

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
        $goal = $this->input('goal');
        $weight = (float) $this->input('weight_kg');

        $targetRules = ['nullable', 'numeric', 'min:30', 'max:300'];

        if ($goal === Goal::Lose->value && $weight > 0) {
            $targetRules[] = 'lt:weight_kg';
        } elseif ($goal === Goal::Gain->value && $weight > 0) {
            $targetRules[] = 'gt:weight_kg';
        }

        return [
            'age' => ['required', 'integer', 'min:13', 'max:120'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'height_cm' => ['required', 'integer', 'min:100', 'max:250'],
            'weight_kg' => ['required', 'numeric', 'min:30', 'max:300'],
            'goal' => ['required', Rule::enum(Goal::class)],
            'target_weight_kg' => $targetRules,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'target_weight_kg.lt' => 'Target weight must be lower than your current weight for a weight loss goal.',
            'target_weight_kg.gt' => 'Target weight must be higher than your current weight for a weight gain goal.',
        ];
    }
}

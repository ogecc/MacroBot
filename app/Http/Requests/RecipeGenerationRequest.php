<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecipeGenerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'min:3', 'max:500'],
            'meal_type' => ['nullable', 'string', 'in:breakfast,lunch,dinner,snack'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'eaten_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:150'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.calories' => ['required', 'integer', 'min:0'],
            'items.*.protein_g' => ['required', 'numeric', 'min:0'],
            'items.*.carbs_g' => ['required', 'numeric', 'min:0'],
            'items.*.fat_g' => ['required', 'numeric', 'min:0'],
        ];
    }
}

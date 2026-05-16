<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MealAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,webp,heic', 'max:10240'],
            'description' => ['nullable', 'string', 'min:3', 'max:1000'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            if (! $this->hasFile('image') && ! $this->filled('description')) {
                $validator->errors()->add('image', 'Please provide an image or a description.');
            }
        });
    }
}

<?php

namespace App\Http\Requests\Superadmin;

use Illuminate\Foundation\Http\FormRequest;

class PackageStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('Superadmin');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'duration_days' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Package name is required.',
            'price.required' => 'Package price is required.',
            'price.min' => 'Price cannot be negative.',
            'duration_days.required' => 'Duration is required.',
            'duration_days.min' => 'Duration must be at least 1 day.',
        ];
    }
}

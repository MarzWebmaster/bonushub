<?php

namespace App\Http\Requests\Superadmin;

use Illuminate\Foundation\Http\FormRequest;

class PackageUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('superadmin');
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price' => 'sometimes|required|numeric|min:0',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'duration_days' => 'sometimes|required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ];
    }
}

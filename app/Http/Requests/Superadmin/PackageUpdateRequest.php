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
            'price' => 'sometimes|required|numeric|min:0',
            'branch_limit' => 'nullable|integer|min:-1',
            'staff_limit' => 'nullable|integer|min:-1',
            'giveaway_limit' => 'nullable|integer|min:-1',
            'task_limit' => 'nullable|integer|min:-1',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
        ];
    }
}

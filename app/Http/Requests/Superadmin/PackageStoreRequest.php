<?php

namespace App\Http\Requests\Superadmin;

use Illuminate\Foundation\Http\FormRequest;

class PackageStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('superadmin');
    }

    /**
     * The package form sends name/price/limits/features. The old rules required `duration_days` (no such column and
     * the form never sends it, so every create from the UI failed with 422) and had NO limit fields, so validated()
     * dropped branch/staff/giveaway/task limits. -1 = unlimited.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'branch_limit' => 'nullable|integer|min:-1',
            'staff_limit' => 'nullable|integer|min:-1',
            'giveaway_limit' => 'nullable|integer|min:-1',
            'task_limit' => 'nullable|integer|min:-1',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Package name is required.',
            'price.required' => 'Package price is required.',
            'price.min' => 'Price cannot be negative.',
        ];
    }
}

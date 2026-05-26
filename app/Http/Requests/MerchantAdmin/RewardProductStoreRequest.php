<?php

namespace App\Http\Requests\MerchantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class RewardProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('merchant');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'points_required' => 'required|integer|min:1',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Reward product name is required.',
            'points_required.required' => 'Points required is required.',
            'points_required.min' => 'Points required must be at least 1.',
            'stock.min' => 'Stock cannot be negative.',
            'image.max' => 'Image must not exceed 2MB.',
        ];
    }
}

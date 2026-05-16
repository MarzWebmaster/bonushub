<?php

namespace App\Http\Requests\MerchantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class RewardProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('Shop Admin');
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'points_required' => 'sometimes|required|integer|min:1',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ];
    }
}

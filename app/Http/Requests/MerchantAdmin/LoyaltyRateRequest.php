<?php

namespace App\Http\Requests\MerchantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class LoyaltyRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('Shop Admin');
    }

    public function rules(): array
    {
        return [
            'points_per_currency' => 'required|numeric|min:0.01|max:1000',
            'currency_unit' => 'required|string|max:10',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'points_per_currency.required' => 'Points per currency rate is required.',
            'points_per_currency.min' => 'Rate must be at least 0.01.',
        ];
    }
}

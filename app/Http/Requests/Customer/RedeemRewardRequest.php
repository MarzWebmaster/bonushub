<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class RedeemRewardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('customer');
    }

    public function rules(): array
    {
        return [
            'merchant_id' => 'required|integer|exists:merchants,id',
            'reward_product_id' => 'required|integer|exists:reward_products,id',
            'quantity' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'merchant_id.exists' => 'The selected merchant does not exist.',
            'reward_product_id.exists' => 'The selected reward product does not exist.',
        ];
    }
}

<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class RedeemPointsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('staff');
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:users,id',
            'reward_product_id' => 'required|integer|exists:reward_products,id',
            'quantity' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.exists' => 'The selected customer does not exist.',
            'reward_product_id.exists' => 'The selected reward product does not exist.',
        ];
    }
}

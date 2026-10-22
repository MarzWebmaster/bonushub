<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class RedeemPointsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['staff', 'merchant']);
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'reward_id'   => 'required|integer|exists:merchant_rewards,id',   // was exists:reward_products (table does not exist)
            'quantity'    => 'nullable|integer|min:1|max:100',
        ];
    }
}

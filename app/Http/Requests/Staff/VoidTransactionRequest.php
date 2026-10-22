<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class VoidTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['staff', 'merchant']);
    }

    public function rules(): array
    {
        return [
            'transaction_id' => 'required|integer|exists:points_transactions,id',   // was exists:transactions (does not exist)
            'reason'         => 'required|string|min:3|max:500',
        ];
    }
}

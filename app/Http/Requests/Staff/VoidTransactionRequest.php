<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class VoidTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('staff');
    }

    public function rules(): array
    {
        return [
            'transaction_id' => 'required|integer|exists:transactions,id',
            'reason' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'transaction_id.exists' => 'The selected transaction does not exist.',
            'reason.required' => 'A reason is required to void a transaction.',
        ];
    }
}

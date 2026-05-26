<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class CustomerLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('staff');
    }

    public function rules(): array
    {
        return [
            'phone' => 'required|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Phone number is required to look up a customer.',
        ];
    }
}

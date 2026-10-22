<?php

namespace App\Http\Requests\Superadmin;

use Illuminate\Foundation\Http\FormRequest;

class MerchantUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('superadmin');
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            // ignore the merchant owner's own row (route parameter is {id}, the merchant id)
            'email' => ['sometimes', 'required', 'email', 'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore(
                    \App\Models\User::where('merchant_id', $this->route('id'))->role('merchant')->orderBy('id')->value('id'))],
            'password' => 'nullable|string|min:8',
            'merchant_name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'package_id' => 'nullable|integer|exists:packages,id',
        ];
    }
}

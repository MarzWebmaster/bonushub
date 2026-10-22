<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('customer');
    }

    public function rules(): array
    {
        $user = $this->user();
        // the customer row is linked to the user by e-mail; ignore it in the customers.* unique checks
        $customerId = \App\Models\Customer::where('email', $user->email)->value('id');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', 'max:255', 'not_regex:/[\r\n]/',
                Rule::unique('users', 'email')->ignore($user->id),
                Rule::unique('customers', 'email')->ignore($customerId)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('customers', 'phone')->ignore($customerId)],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already taken.',
        ];
    }
}

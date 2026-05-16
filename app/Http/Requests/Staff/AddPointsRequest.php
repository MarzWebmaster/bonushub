<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class AddPointsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('Staff');
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:users,id',
            'points' => 'required|integer|min:1|max:100000',
            'reason' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.exists' => 'The selected customer does not exist.',
            'points.min' => 'Points must be at least 1.',
            'points.max' => 'Points cannot exceed 100,000 in a single transaction.',
        ];
    }
}

<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class AddPointsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['staff', 'merchant']);
    }

    public function rules(): array
    {
        return [
            // customers.id (NOT users.id); membership of the staff member's merchant is checked in the controller
            'customer_id' => 'required|integer|exists:customers,id',
            'points'      => 'required|integer|min:1|max:100000',
            'reference'   => 'nullable|string|max:100',
            'notes'       => 'nullable|string|max:500',
        ];
    }
}

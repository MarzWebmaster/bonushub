<?php

namespace App\Http\Requests\Merchant;

use Illuminate\Foundation\Http\FormRequest;

class CampaignLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'   => 'required|string|max:255',
            'medium' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,inactive',
            'expires_at' => 'nullable|date|after:now',
        ];
    }
}

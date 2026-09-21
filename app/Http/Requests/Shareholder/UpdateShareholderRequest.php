<?php

namespace App\Http\Requests\Shareholder;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShareholderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('shareholder') ?? $this->route('id');

        return [
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:30',
            'id_card_number' => 'required|string|max:50|unique:shareholders,id_card_number,' . $id,
            'address'        => 'nullable|string',
            'notes'          => 'nullable|string',
            'status'         => 'required|in:active,inactive',
        ];
    }
}

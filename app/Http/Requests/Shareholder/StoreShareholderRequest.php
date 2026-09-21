<?php

namespace App\Http\Requests\Shareholder;

use Illuminate\Foundation\Http\FormRequest;

class StoreShareholderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:30',
            'id_card_number' => 'required|string|max:50|unique:shareholders,id_card_number',
            'address'        => 'nullable|string',
            'notes'          => 'nullable|string',
            'status'         => 'required|in:active,inactive',

            // Data saham perdana (opsional saat pendaftaran)
            'share_code'              => 'nullable|string|max:50',
            'entity_name'             => 'nullable|string|max:255',
            'total_shares'            => 'nullable|integer|min:1',
            'nominal_value_per_share' => 'nullable|numeric|min:100',
            'acquisition_date'        => 'nullable|date',
            'certificate_number'      => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'Nama pemegang saham wajib diisi.',
            'id_card_number.required' => 'Nomor identitas NIK/KTP wajib diisi.',
            'id_card_number.unique'   => 'Nomor identitas NIK/KTP sudah terdaftar.',
            'total_shares.min'        => 'Jumlah lembar saham minimal 1 lembar.',
        ];
    }
}

<?php

namespace App\Http\Requests\ShareHolding;

use Illuminate\Foundation\Http\FormRequest;

class StoreShareHoldingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shareholder_id'          => 'required|exists:shareholders,id',
            'share_code'              => 'required|string|max:50',
            'entity_name'             => 'required|string|max:255',
            'total_shares'            => 'required|integer|min:1',
            'nominal_value_per_share' => 'required|numeric|min:100',
            'acquisition_date'        => 'required|date',
            'certificate_number'      => 'nullable|string|max:100',
            'status'                  => 'required|in:active,transferred,sold',
            'notes'                   => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'share_code.required'   => 'Kode saham wajib diisi (misal: CIO-CORE-01).',
            'entity_name.required'  => 'Nama entitas/unit saham wajib diisi.',
            'total_shares.required' => 'Jumlah lembar saham wajib diisi.',
            'total_shares.min'      => 'Jumlah lembar saham minimal 1 lembar.',
        ];
    }
}

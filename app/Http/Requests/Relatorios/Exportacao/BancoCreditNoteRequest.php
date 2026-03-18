<?php

namespace App\Http\Requests\Relatorios\Exportacao;

use Illuminate\Foundation\Http\FormRequest;

class BancoCreditNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cod_empresa'    => ['required', 'string', 'regex:/^\d{2}$/'],
            'num_nc'         => ['required', 'numeric'],
            'ano_nc'         => ['required', 'numeric', 'digits:4'],
            'account_name'   => ['nullable', 'string', 'max:200'],
            'bank_name'      => ['nullable', 'string', 'max:200'],
            'account_type'   => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'iban'           => ['nullable', 'string', 'max:100'],
            'swift_code'     => ['nullable', 'string', 'max:50'],
            'branch'         => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'cod_empresa.required' => 'A empresa é obrigatória.',
            'num_nc.required'      => 'O número da NC é obrigatório.',
            'ano_nc.required'      => 'O ano da NC é obrigatório.',
        ];
    }
}

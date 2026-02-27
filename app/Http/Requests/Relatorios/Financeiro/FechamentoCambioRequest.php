<?php

namespace App\Http\Requests\Relatorios\Financeiro;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FechamentoCambioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dat_cambio'     => ['required', 'date'],
            'cod_banco'      => ['required', 'string'],
            'fech'           => ['nullable', 'string'],
            'ies_due'        => ['nullable', Rule::in(['S', 'N'])],
            'num_seq_cambio' => ['nullable', 'string'],
            'num_contrato'   => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'dat_cambio.required' => 'A data do câmbio é obrigatória.',
            'dat_cambio.date'     => 'A data do câmbio deve ser uma data válida.',
            'cod_banco.required'  => 'O banco é obrigatório.',
        ];
    }
}

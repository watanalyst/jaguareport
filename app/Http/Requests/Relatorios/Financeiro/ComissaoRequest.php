<?php

namespace App\Http\Requests\Relatorios\Financeiro;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComissaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ep'         => ['nullable', 'string', 'max:2'],
            'repres_ini' => ['required', 'digits_between:1,10'],
            'repres_fim' => ['required', 'digits_between:1,10'],
            'televendas' => ['nullable', Rule::in(['S', 'N'])],
            'clt'        => ['nullable', Rule::in(['S', 'N'])],
            'fr'         => ['nullable', Rule::in(['C', 'F', 'AMBOS'])],
            'data_ini'   => ['nullable', 'date'],
            'data_fim'   => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'repres_ini.required'       => 'O representante inicial é obrigatório.',
            'repres_ini.digits_between' => 'O representante inicial deve ter entre 1 e 10 dígitos.',
            'repres_fim.required'       => 'O representante final é obrigatório.',
            'repres_fim.digits_between' => 'O representante final deve ter entre 1 e 10 dígitos.',
        ];
    }
}

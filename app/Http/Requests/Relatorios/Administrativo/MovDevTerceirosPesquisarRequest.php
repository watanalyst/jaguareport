<?php

namespace App\Http\Requests\Relatorios\Administrativo;

use Illuminate\Foundation\Http\FormRequest;

class MovDevTerceirosPesquisarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cod_empresa' => ['required', 'string'],
            'dat_movto'   => ['required', 'date'],
            'cod_item'    => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'cod_empresa.required' => 'Selecione ao menos uma empresa.',
            'dat_movto.required'   => 'A data de movimento é obrigatória.',
        ];
    }
}

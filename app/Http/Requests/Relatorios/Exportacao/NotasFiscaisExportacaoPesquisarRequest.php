<?php

namespace App\Http\Requests\Relatorios\Exportacao;

use Illuminate\Foundation\Http\FormRequest;

class NotasFiscaisExportacaoPesquisarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cod_empresa'     => ['required', 'string'],
            'dat_emissao_ini' => ['required', 'date'],
            'dat_emissao_fim' => ['required', 'date'],
            'num_processo'    => ['nullable', 'numeric'],
            'ano_processo'    => ['nullable', 'numeric'],
            'embarque'        => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'cod_empresa.required'     => 'Selecione ao menos uma empresa.',
            'dat_emissao_ini.required' => 'A data de início é obrigatória.',
            'dat_emissao_fim.required' => 'A data final é obrigatória.',
        ];
    }
}

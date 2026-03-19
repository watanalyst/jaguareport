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
            'cod_empresa'     => ['nullable', 'string'],
            'dat_emissao_ini' => ['nullable', 'date'],
            'dat_emissao_fim' => ['nullable', 'date'],
            'num_processo'    => ['nullable', 'numeric'],
            'ano_processo'    => ['nullable', 'numeric'],
            'embarque'        => ['nullable', 'string'],
        ];
    }
}

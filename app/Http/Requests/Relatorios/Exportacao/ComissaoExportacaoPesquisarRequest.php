<?php

namespace App\Http\Requests\Relatorios\Exportacao;

use Illuminate\Foundation\Http\FormRequest;

class ComissaoExportacaoPesquisarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom_agente'      => ['nullable', 'string'],
            'dat_confirm_ini' => ['nullable', 'date'],
            'dat_confirm_fim' => ['nullable', 'date'],
        ];
    }
}

<?php

namespace App\Http\Requests\Relatorios\Exportacao;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmbarquesExportacaoPesquisarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa'       => ['required', 'string', 'regex:/^\d{2}(,\d{2})*$/'],
            'dt_prev_ini'   => ['nullable', 'date'],
            'dt_prev_fim'   => ['nullable', 'date', 'after_or_equal:dt_prev_ini'],
            'situacao_ped'  => ['nullable', 'string'],
            'cod_item'      => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'empresa.required'          => 'Selecione ao menos uma empresa.',
            'empresa.regex'             => 'Formato de empresa inválido.',
            'dt_prev_ini.date'          => 'A data inicial deve ser uma data válida.',
            'dt_prev_fim.date'          => 'A data final deve ser uma data válida.',
            'dt_prev_fim.after_or_equal' => 'A data final deve ser maior ou igual à data inicial.',
        ];
    }
}

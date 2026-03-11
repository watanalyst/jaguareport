<?php

namespace App\Http\Requests\Relatorios\Exportacao;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessosExportacaoPesquisarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa'      => ['required', 'string'],
            'num_processo'  => ['required', 'string', 'regex:/^[\d\.\s]+(,[\d\.\s]+)*$/'],
            'ano_processo'  => ['required', 'string'],
            'dat_inclusao'  => ['nullable', 'date'],
            'cod_situacao'  => ['nullable', Rule::in(['', 'A', 'C', 'F', 'O', 'P'])],
        ];
    }

    public function messages(): array
    {
        return [
            'empresa.required'   => 'O campo Empresa é obrigatório.',
            'num_processo.required' => 'O campo Num Processo é obrigatório.',
            'num_processo.regex'    => 'Informe números de processo válidos separados por vírgula.',
            'ano_processo.required' => 'O campo Ano Processo é obrigatório.',
            'dat_inclusao.date'  => 'A data de inclusão deve ser uma data válida.',
            'cod_situacao.in'    => 'Situação inválida.',
        ];
    }
}

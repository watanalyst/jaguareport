<?php

namespace App\Http\Requests\Relatorios\Exportacao;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessosExportacaoDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doc_type'          => ['required', 'string', Rule::in(array_keys(config('export_documents')))],
            'copy_type'         => ['nullable', Rule::in(['original', 'copia'])],
            'rows'              => ['required', 'array', 'min:1'],
            'rows.*.empresa'    => ['required', 'string'],
            'rows.*.processo'   => ['required', 'string'],
            'rows.*.embarque'   => ['required', 'string'],
            'rows.*.ano'        => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'doc_type.required' => 'O tipo de documento é obrigatório.',
            'doc_type.in'       => 'Tipo de documento inválido.',
            'rows.required'     => 'Selecione pelo menos um embarque.',
            'rows.min'          => 'Selecione pelo menos um embarque.',
        ];
    }
}

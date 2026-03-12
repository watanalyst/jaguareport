<?php

namespace App\Http\Requests\Relatorios\Financeiro;

use Illuminate\Foundation\Http\FormRequest;

class ComissaoRepresentantePesquisarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'emp'        => ['nullable', 'string', 'size:2'],
            'data_ini'   => ['nullable', 'date'],
            'data_fim'   => ['nullable', 'date', 'after_or_equal:data_ini'],
            'cod_repres' => ['nullable', 'string', 'regex:/^[\d\s,]+$/'],
            'status'     => ['nullable', 'string', 'in:S,N'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $fields = ['emp', 'data_ini', 'data_fim', 'cod_repres'];
            $hasFilter = collect($fields)->contains(fn ($f) => $this->filled($f));

            if (! $hasFilter) {
                $validator->errors()->add('emp', 'Informe pelo menos um filtro (empresa, data ou representante).');
            }
        });
    }

    public function messages(): array
    {
        return [
            'emp.size'                => 'Empresa deve ter 2 caracteres.',
            'data_ini.date'           => 'Data início inválida.',
            'data_fim.date'           => 'Data fim inválida.',
            'data_fim.after_or_equal' => 'Data fim deve ser maior ou igual à data início.',
            'cod_repres.regex'        => 'Representante deve conter apenas números separados por vírgula.',
        ];
    }
}

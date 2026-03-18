<?php

namespace App\Http\Requests\Relatorios\Exportacao;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreditNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'num_nc'  => preg_replace('/\D/', '', $this->num_nc ?? ''),
            'ano_nc'  => preg_replace('/\D/', '', $this->ano_nc ?? ''),
        ]);
    }

    public function rules(): array
    {
        return [
            'cod_empresa'  => ['required', 'string', 'regex:/^\d{2}$/'],
            'num_nc'       => ['required', 'string', 'regex:/^\d{1,4}$/'],
            'ano_nc'       => ['required', 'string', 'regex:/^\d{4}$/'],
            'dados_banco'  => ['required', Rule::in(['S', 'N'])],
        ];
    }

    public function messages(): array
    {
        return [
            'cod_empresa.required' => 'A empresa é obrigatória.',
            'num_nc.required'      => 'O número da NC é obrigatório.',
            'num_nc.regex'         => 'O número da NC deve ter até 4 dígitos numéricos.',
            'ano_nc.required'      => 'O ano da NC é obrigatório.',
            'ano_nc.regex'         => 'O ano da NC deve ter 4 dígitos numéricos.',
            'dados_banco.required' => 'O campo Dados Banco é obrigatório.',
        ];
    }
}

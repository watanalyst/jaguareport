<?php

namespace App\Http\Requests\Relatorios\Exportacao;

use Illuminate\Foundation\Http\FormRequest;

class DebitNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'num_nd' => preg_replace('/\D/', '', $this->num_nd ?? ''),
            'ano_nd' => preg_replace('/\D/', '', $this->ano_nd ?? ''),
        ]);
    }

    public function rules(): array
    {
        return [
            'cod_empresa' => ['required', 'string', 'regex:/^\d{2}$/'],
            'num_nd'      => ['required', 'string', 'regex:/^\d{1,4}$/'],
            'ano_nd'      => ['required', 'string', 'regex:/^\d{4}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'cod_empresa.required' => 'A empresa é obrigatória.',
            'num_nd.required'      => 'O número da ND é obrigatório.',
            'num_nd.regex'         => 'O número da ND deve ter até 4 dígitos numéricos.',
            'ano_nd.required'      => 'O ano da ND é obrigatório.',
            'ano_nd.regex'         => 'O ano da ND deve ter 4 dígitos numéricos.',
        ];
    }
}

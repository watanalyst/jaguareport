<?php

namespace App\Http\Requests\Relatorios\Financeiro;

use Illuminate\Foundation\Http\FormRequest;

class ComissaoRedeconomiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_ini' => ['required', 'date'],
            'data_fim' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_ini.required' => 'A data inicial é obrigatória.',
            'data_ini.date'     => 'A data inicial deve ser uma data válida.',
            'data_fim.required' => 'A data final é obrigatória.',
            'data_fim.date'     => 'A data final deve ser uma data válida.',
        ];
    }
}

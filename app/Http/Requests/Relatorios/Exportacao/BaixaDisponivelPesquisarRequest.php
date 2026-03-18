<?php

namespace App\Http\Requests\Relatorios\Exportacao;

use Illuminate\Foundation\Http\FormRequest;

class BaixaDisponivelPesquisarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dat_conf_pgto_ini' => ['required', 'date'],
            'dat_conf_pgto_fim' => ['required', 'date'],
        ];
    }
}

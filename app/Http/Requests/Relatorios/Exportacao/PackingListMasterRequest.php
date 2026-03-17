<?php

namespace App\Http\Requests\Relatorios\Exportacao;

use Illuminate\Foundation\Http\FormRequest;

class PackingListMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cod_empresa' => ['required', 'string', 'max:2'],
            'processo'    => ['required', 'string', 'max:5'],
            'ano'         => ['required', 'string', 'max:4'],
            'embarque'    => ['required', 'string', 'max:1'],
            'num_pedido'  => ['required', 'numeric'],
            'cod_item'    => ['required', 'string', 'max:15'],
        ];
    }
}

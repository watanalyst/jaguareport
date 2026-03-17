<?php

namespace App\Http\Requests\Relatorios\Exportacao;

use Illuminate\Foundation\Http\FormRequest;

class PackingListDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'production_date' => ['required', 'date'],
            'date_expiry'     => ['required', 'date'],
            'cartons'         => ['required', 'integer', 'min:0'],
            'net_weight'      => ['required', 'numeric', 'min:0'],
            'gross_weight'    => ['required', 'numeric', 'min:0'],
            'lots'            => ['nullable', 'string', 'max:10'],
            'palete'          => ['nullable', 'string', 'max:20'],
        ];
    }
}

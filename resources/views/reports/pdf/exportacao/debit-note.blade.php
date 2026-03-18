@php
    use Carbon\Carbon;

    // --- Branding ---
    $logo = public_path($branding['logo']);
    $ass  = public_path($branding['signature']);

    $h = $header;

    // --- Formatting ---
    $datInclusao = !empty($h->dat_inclusao) ? Carbon::parse($h->dat_inclusao)->format('d/m/Y') : '';
    $valTotalFormatted = number_format($h->val_total_nd, 2, '.', ',');

    // --- Labels based on currency ---
    $isUsd = trim($h->den_moeda_abrev) === 'USD';
    $lblDesc = $isUsd ? 'Description' : 'Descrição';
    $lblVal  = $isUsd ? 'Amount' : 'Valor';
    $lblTot  = 'Total';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Debit Note {{ $h->num_nd }}/{{ $h->ano_nd }}</title>
    <style>
        @page { margin: 30px 25px 100px 25px; }

        * { font-family: Helvetica, Arial, sans-serif; font-size: 11px; line-height: 1.3; }

        body { margin: 0; padding: 0; color: #333; }

        table { border-collapse: separate; border-spacing: 0; width: 100%; }

        th, td { vertical-align: top; padding: 2px 4px; font-size: 11px; }

        .b-t  { border-top: 1px solid #000; }
        .b-b  { border-bottom: 1px solid #000; }
        .b-l  { border-left: 1px solid #000; }
        .b-r  { border-right: 1px solid #000; }

        .bg-gray { background-color: #d3d3d3; }
        .fs-lg { font-size: 16px; }
        .fs-md { font-size: 14px; }

        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
        }
    </style>
</head>
<body>

{{-- ══════════════════════════════════════════════════════════════
     FIXED FOOTER (Signature)
     ══════════════════════════════════════════════════════════════ --}}
<div class="page-footer">
    <table cellspacing="0" cellpadding="3">
        <tr>
            <td width="25%"></td>
            <td width="50%" align="center">
                @if($ass && file_exists($ass))
                    <img src="{{ $ass }}" alt="Assinatura" width="200">
                @endif
            </td>
            <td width="25%"></td>
        </tr>
        <tr>
            <td colspan="3" align="center" style="padding-top: 5px;" class="b-t">Nome Completo do Responsável da Empresa Emitente</td>
        </tr>
    </table>
</div>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 1: COMPANY HEADER
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="2">
    <tr>
        <td width="18%" align="left">
            @if($logo && file_exists($logo))
                <img src="{{ $logo }}" alt="LOGO" width="100">
            @endif
        </td>
        <td width="64%" align="center">
            <table cellspacing="0" cellpadding="2">
                <tr>
                    <th align="center" class="fs-md">{{ trim($h->den_razao_social) }}</th>
                </tr>
                <tr>
                    <th align="center">{{ trim($h->end_empresa) }} {{ trim($h->cod_cep) }} - {{ trim($h->den_munic) }} - {{ trim($h->uf) }}</th>
                </tr>
            </table>
        </td>
        <td width="18%" align="right">
            @if($logo && file_exists($logo))
                <img src="{{ $logo }}" alt="LOGO" width="100">
            @endif
        </td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <th colspan="3" align="center" class="fs-lg bg-gray b-t b-l b-r">DEBIT NOTE</th>
    </tr>
    <tr>
        <th colspan="3" align="center" class="bg-gray b-b b-l b-r">N&ordm; {{ $h->num_nd }} / {{ $h->ano_nd }}</th>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 2: ISSUER DETAIL
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="2">
    <tr>
        <th colspan="3" align="left">Issuer Detail:</th>
    </tr>
    <tr>
        <td width="15%" class="b-t b-l">Razão Social:</td>
        <td width="55%" class="b-t">{{ trim($h->den_razao_social) }}</td>
        <td width="30%" class="b-t b-r"></td>
    </tr>
    <tr>
        <td class="b-l">Endereço:</td>
        <td>{{ trim($h->end_empresa) }}</td>
        <td class="b-r"></td>
    </tr>
    <tr>
        <td class="b-l">Cidade:</td>
        <td>{{ trim($h->den_munic) }}</td>
        <td class="b-r">UF: {{ trim($h->uf) }}</td>
    </tr>
    <tr>
        <td class="b-b b-l">CNPJ:</td>
        <td class="b-b">{{ trim($h->cnpj) }}</td>
        <td class="b-b b-r">Inscrição Estadual: {{ trim($h->ins_estadual) }}</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
    <tr>
        <th colspan="3" align="left">Date of Issue: {{ $datInclusao }}</th>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 3: RECIPIENT DATA
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="2">
    <tr>
        <th align="left">Recipient Data:</th>
    </tr>
    <tr>
        <td class="b-t b-l b-r">{{ trim($h->nom_cliente) }}</td>
    </tr>
    <tr>
        <td class="b-l b-r">{{ trim($h->end_cliente) }}</td>
    </tr>
    <tr>
        <td class="b-b b-l b-r">{{ trim($h->den_bairro) }}</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
    <tr>
        <td>Messrs.</td>
    </tr>
    <tr>
        <td>We hereby request the reimbursement of the expenses detailed below.</td>
    </tr>
    <tr>
        <td>{{ trim($h->obs) }}</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 4: EXPENSE BREAKDOWN
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <th align="left" colspan="2">Expense Breakdown:</th>
    </tr>
    <tr>
        <th width="80%" align="left" class="b-t b-b b-l b-r bg-gray">{{ $lblDesc }}</th>
        <th width="20%" align="center" class="b-t b-b b-r bg-gray">{{ $lblVal }}</th>
    </tr>
    @foreach ($items as $item)
    <tr>
        <td align="left" class="b-b b-l b-r">{{ trim($item->descricao_nd) }}</td>
        <td align="right" class="b-b b-r">{{ trim($h->den_moeda_abrev) }} {{ number_format($item->val_unit_nd, 2, '.', ',') }}</td>
    </tr>
    @endforeach
    {{-- Bank details --}}
    <tr><td colspan="2" class="b-l b-r" style="height: 5px;"></td></tr>
    <tr>
        <th align="left" colspan="2" class="b-l b-r">{{ trim($h->bank) }} SWIFT CODE: {{ trim($h->swift_code_56) }}</th>
    </tr>
    <tr>
        <th align="left" colspan="2" class="b-l b-r">{{ trim($h->number_56) }}</th>
    </tr>
    <tr>
        <th align="left" colspan="2" class="b-l b-r">{{ trim($h->account_57) }}</th>
    </tr>
    @if(trim($h->branch_number ?? ''))
    <tr>
        <th align="left" colspan="2" class="b-l b-r">BRANCHNUMBER: {{ trim($h->branch_number) }}</th>
    </tr>
    @endif
    @if(trim($h->account_number ?? ''))
    <tr>
        <th align="left" colspan="2" class="b-l b-r">ACCOUNTNUMBER: {{ trim($h->account_number) }}</th>
    </tr>
    @endif
    @if(trim($h->iban ?? ''))
    <tr>
        <th align="left" colspan="2" class="b-l b-r">IBAN: {{ trim($h->iban) }}</th>
    </tr>
    @endif
    <tr><td colspan="2" class="b-b b-l b-r" style="height: 5px;"></td></tr>
    {{-- Total --}}
    <tr>
        <th align="right" class="b-b b-l b-r bg-gray">{{ $lblTot }}:</th>
        <th align="right" class="b-b b-r">{{ trim($h->den_moeda_abrev) }} {{ $valTotalFormatted }}</th>
    </tr>
</table>

</body>
</html>

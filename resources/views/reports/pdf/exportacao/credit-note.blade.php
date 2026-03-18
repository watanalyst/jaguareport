@php
    use Carbon\Carbon;

    // --- Branding ---
    $bg   = $branding['bg_color'];
    $fc   = $branding['font_color'];
    $logo = public_path($branding['logo']);
    $ass  = public_path($branding['signature']);

    $h = $header;

    // --- Formatting ---
    $datAtual = now()->locale('en')->isoFormat('MMMM D, YYYY');
    $valTotalFormatted = number_format($h->val_total_nc, 2, '.', ',');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Credit Note {{ $h->num_nc }}/{{ $h->ano_nc }}</title>
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
        .red { color: red; }
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
            <td>Respectfully,</td>
        </tr>
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
        <th colspan="3" align="center" class="fs-lg bg-gray b-t b-l b-r">CREDIT NOTE</th>
    </tr>
    <tr>
        <th colspan="3" align="center" class="bg-gray b-b b-l b-r">N&ordm; {{ $h->num_nc }} / {{ $h->ano_nc }}</th>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 2: DESTINATION + AMOUNT
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="2">
    <tr>
        <th align="right">Jaguapitã-PR - Brazil, {{ $datAtual }}</th>
    </tr>
    <tr>
        <th align="left">TO:</th>
    </tr>
    <tr>
        <td>{{ trim($h->nom_cliente) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->end_cliente) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->den_bairro) }}</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
    <tr>
        <th align="left" class="red">AMOUNT: {{ trim($h->den_moeda_abrev) }} {{ $valTotalFormatted }}</th>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
    <tr>
        <td class="b-b"></td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
    <tr>
        <td>{{ trim($h->obs) }}</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 3: ITEMS TABLE
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <th align="left" class="b-t b-b b-l b-r bg-gray">Description</th>
        <th align="center" class="b-t b-b b-r bg-gray">USD Value</th>
    </tr>
    @foreach ($items as $item)
    <tr>
        <td align="left" class="b-b b-l b-r">{{ trim($item->descricao_nc) }}</td>
        <td align="right" class="b-b b-r">{{ number_format($item->val_unit_nc, 2, '.', ',') }}</td>
    </tr>
    @endforeach
    <tr>
        <th align="right" class="b-b b-l b-r">Total Claim Value:</th>
        <th align="right" class="b-b b-r">{{ trim($h->den_moeda_abrev) }} {{ $valTotalFormatted }}</th>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 4: BANK DETAILS OR DISCOUNT MESSAGE
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr><td style="height: 15px;"></td></tr>
@if ($dados_banco === 'S')
    <tr>
        <th align="left"><u>Bank Details:</u></th>
    </tr>
    <tr><th align="left">Account Name: {{ trim($h->account_name_cli) }}</th></tr>
    <tr><th align="left">Bank name: {{ trim($h->bank_name_cli) }}</th></tr>
    <tr><th align="left">Account Type: {{ trim($h->account_type_cli) }}</th></tr>
    <tr><th align="left">Account Number: {{ trim($h->account_number_cli) }}</th></tr>
    <tr><th align="left">IBAN: {{ trim($h->iban_cli) }}</th></tr>
    <tr><th align="left">Swift code: {{ trim($h->swift_code_cli) }}</th></tr>
    <tr><th align="left">Branch: {{ trim($h->branch_cli) }}</th></tr>
@else
    <tr>
        <th align="left">THE AMOUNT ABOVE MENTIONED, WILL BE DISCOUNTED IN NEXT BILL.<br>IF YOU HAVE ANY DOUBT, DO NOT HESITATE TO CONTACT US.</th>
    </tr>
@endif
</table>

</body>
</html>

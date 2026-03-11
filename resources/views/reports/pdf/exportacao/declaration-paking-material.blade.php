@php
    use Carbon\Carbon;

    // --- Branding ---
    $bg   = $branding['bg_color'];
    $fc   = $branding['font_color'];
    $logo = public_path($branding['logo']);
    $ass  = public_path($branding['signature']);

    $h = $header;

    // --- Date Formatting ---
    $datAtualiz = $h->dat_atualiz ? Carbon::parse($h->dat_atualiz)->locale('en')->isoFormat('MMMM D, YYYY') : '';

    // --- Items (descriptions concatenadas) ---
    $descriptions = [];

    foreach ($items as $item) {
        $desc = trim($item->den_item_int ?? '');
        if ($desc !== '') {
            $descriptions[] = $desc;
        }
    }

    $empresa = trim($h->den_razao_social ?? '');

    // --- Copy/Original Stamp ---
    $stampPath = null;
    if (($copyType ?? null) === 'original') {
        $stampPath = public_path('img/stamp-original.png');
    } elseif (($copyType ?? null) === 'copia') {
        $stampPath = public_path('img/stamp-copy.png');
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Declaration Paking Material {{ trim($h->proforma) }}-{{ trim($h->embarque) }}</title>
    <style>
        @page { margin: 30px 25px 60px 25px; }

        * { font-family: Helvetica, Arial, sans-serif; font-size: 10.5px; line-height: 1.3; }

        body { margin: 0; padding: 0; color: #333; }

        table { border-collapse: separate; border-spacing: 0; width: 100%; }

        th, td { vertical-align: top; padding: 2px 4px; font-size: 10.5px; }

        .b-b { border-bottom: 1px solid #000; }

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
     FIXED FOOTER (appears on every page at bottom)
     ══════════════════════════════════════════════════════════════ --}}
<div class="page-footer">
    <table cellspacing="0" cellpadding="3">
        <tr>
            <td colspan="3" class="b-b"></td>
        </tr>
        <tr>
            <td width="25%"></td>
            <td width="50%" align="center"><b>{{ $empresa }}</b></td>
            <td width="25%"></td>
        </tr>
        <tr>
            <td></td>
            <td align="center">EXPORT DEPARTMENT</td>
            <td></td>
        </tr>
    </table>
</div>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 1: COMPANY HEADER
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="2">
    <tr>
        <td width="15%" rowspan="4" align="center">
            @if($logo && file_exists($logo))
                <img src="{{ $logo }}" alt="LOGO" width="100">
            @endif
        </td>
        <td width="13%" rowspan="4"></td>
        <th width="30%" align="center" style="font-size: 16px;">{{ trim($h->marca ?? '') }}</th>
        <td width="13%" rowspan="4" align="right">
            @if($stampPath && file_exists($stampPath))
                <img src="{{ $stampPath }}" alt="STAMP" width="120">
            @endif
        </td>
        <td width="15%" rowspan="4" align="center">
            @if($logo && file_exists($logo))
                <img src="{{ $logo }}" alt="LOGO" width="100">
            @endif
        </td>
    </tr>
    <tr>
        <td align="center">{{ $empresa }}</td>
    </tr>
    <tr>
        <td align="center">CNPJ: {{ trim($h->num_cgc ?? '') }}</td>
    </tr>
    <tr>
        <td align="center">PHONE: {{ trim($h->num_telefone ?? '') }}</td>
    </tr>
    <tr>
        <td colspan="5" class="b-b"></td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 2: TITLE
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="2">
    <tr>
        <th align="center" style="font-size: 16px; font-weight: bold;">DECLARATION OF NON-WOOD PACKING MATERIAL</th>
    </tr>
    <tr>
        <th align="center">{{ trim($h->proforma ?? '') }}-{{ trim($h->embarque ?? '') }}</th>
    </tr>
    <tr>
        <td class="b-b"></td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 3: CONSIGNEE (single column)
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr><td style="height: 10px;"></td></tr>
    <tr>
        <th width="25%" align="left">CONSIGNEE:</th>
        <td align="left">{{ trim($h->texto1_consignat ?? '') }}</td>
    </tr>
    <tr>
        <th align="left"></th>
        <td align="left">{{ trim($h->texto2_consignat ?? '') }}</td>
    </tr>
    <tr>
        <th align="left"></th>
        <td align="left">{{ trim($h->texto3_consignat ?? '') }}</td>
    </tr>
    <tr>
        <th align="left"></th>
        <td align="left">{{ trim($h->texto4_consignat ?? '') }}</td>
    </tr>
    <tr>
        <th align="left"></th>
        <td align="left">{{ trim($h->texto5_consignat ?? '') }}</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 4: SHIPMENT DETAILS
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <th width="25%" align="left">PRODUCT:</th>
        <td align="left">
            @foreach($descriptions as $desc)
                {{ $desc }}<br>
            @endforeach
        </td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <th align="left">CONTAINER:</th>
        <td align="left">{{ trim($h->cod_container ?? '') }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <th align="left">SEAL NUMBER (CNTR):</th>
        <td align="left">{{ trim($h->cod_lacre ?? '') }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <th align="left">SEAL NUMBER (SIF):</th>
        <td align="left">{{ trim($h->cod_lacre_sif ?? '') }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <th align="left">VESSEL:</th>
        <td align="left">{{ trim($h->den_navio_aviao ?? '') }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <th align="left">MARKS:</th>
        <td align="left">{{ trim($h->marca ?? '') }}</td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 5: DECLARATION TEXT
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr><td style="height: 20px;"></td></tr>
    <tr>
        <td width="25%"></td>
        <td width="50%" align="center">
            IT IS DECLARED THAT THE ABOVE MENTIONED SHIPMENT DOES NOT CONTAIN ANY WOOD PACKING MATERIALS
        </td>
        <td width="25%"></td>
    </tr>
    <tr><td style="height: 150px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 6: SIGNATURE
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="2">
    <tr>
        <td width="50%" align="center"></td>
        <td width="50%" align="center">
            @if($ass && file_exists($ass))
                <img src="{{ $ass }}" alt="Assinatura" width="150">
            @endif
        </td>
    </tr>
    <tr>
        <td style="height: 05px;"></td>
    </tr>
</table>

</body>
</html>

@php
    use Carbon\Carbon;

    // --- Branding ---
    $bg   = $branding['bg_color'];
    $fc   = $branding['font_color'];
    $logo = public_path($branding['logo']);
    $ass  = public_path($branding['signature']);

    $h = $header;

    // --- Date Formatting ---
    $datEmbarque = $h->dat_embarque ? Carbon::parse($h->dat_embarque)->locale('en')->isoFormat('MMMM D, YYYY') : '';
    $datAtualiz  = $h->dat_atualiz  ? Carbon::parse($h->dat_atualiz)->locale('en')->isoFormat('MMMM D, YYYY') : '';

    $empresa    = trim($h->den_razao_social ?? '');
    $endereco   = trim($h->end_empresa ?? '');
    $municipio  = trim($h->den_munic ?? '');
    $uf         = trim($h->den_uni_feder ?? '');
    $paisEmp    = trim($h->pais_emp ?? '');
    $sif        = trim($h->sif ?? '');
    $paisInt    = trim($h->pais_int ?? '');
    $numBL      = trim($h->num_bl ?? '');
    $numPedido  = trim($h->num_pedido ?? '');
    $contTemp   = trim($h->cont_temperatura ?? '');
    $paisDest   = trim($h->pais_destino ?? '');

    // --- Copy/Original Stamp ---
    $stampPath = null;
    if (($copyType ?? null) === 'original') {
        $stampPath = public_path('img/stamp-original.png');
    } elseif (($copyType ?? null) === 'copia') {
        $stampPath = public_path('img/stamp-copy.png');
    }

    // --- Production dates (range: first UP TO last) ---
    $prodDatesStr = '';
    if (! empty($productionDateRange)) {
        if ($productionDateRange['first'] === $productionDateRange['last']) {
            $prodDatesStr = $productionDateRange['first'];
        } else {
            $prodDatesStr = $productionDateRange['first'] . ' UP TO ' . $productionDateRange['last'];
        }
    }

    // --- Items & Totals ---
    $totQtdCaixa = 0;
    $totPesoLiq  = 0;
    $totPesoBrt  = 0;

    foreach ($items as $item) {
        $totQtdCaixa += (float) ($item->tot_embalag ?? 0);
        $totPesoLiq  += (float) ($item->qtd_pecas_solic ?? 0);
        $totPesoBrt  += (float) ($item->rateio_palete ?? 0);
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Weight Certificate {{ trim($h->proforma) }}-{{ trim($h->embarque) }}</title>
    <style>
        @page { margin: 30px 25px 60px 25px; }

        * { font-family: Helvetica, Arial, sans-serif; font-size: 10px; line-height: 1.3; }

        body { margin: 0; padding: 0; color: #333; }

        table { border-collapse: separate; border-spacing: 0; width: 100%; }

        th, td { vertical-align: top; padding: 2px 4px; font-size: 10px; }

        .b-b { border-bottom: 1px solid #000; }
        .b-t { border-top: 1px solid #000; }
        .b-l { border-left: 1px solid #000; }
        .b-r { border-right: 1px solid #000; }

        .th-header {
            font-weight: bold;
            text-align: center;
            padding: 2px 4px;
            color: {{ $fc }};
            background-color: {{ $bg }};
        }

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
     FIXED FOOTER
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
        <th align="center" style="font-size: 16px; font-weight: bold;">WEIGHT CERTIFICATE</th>
    </tr>
    <tr>
        <th align="center">{{ trim($h->proforma ?? '') }}-{{ trim($h->embarque ?? '') }}</th>
    </tr>
    <tr>
        <td class="b-b"></td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 3: TO WHOM IT MAY CONCERN + CONSIGNEE/EXPORTER
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <th colspan="2" align="center">TO WHOM IT MAY CONCERN</th>
    </tr>
    <tr>
        <th colspan="2" align="right">{{ $datEmbarque }}</th>
    </tr>
    <tr>
        <th width="50%" align="left">CONSIGNEE:</th>
        <th width="50%" align="left">EXPORTED BY:</th>
    </tr>
    <tr>
        <td align="left">{{ trim($h->texto1_consignat ?? '') }}</td>
        <td align="left">{{ $empresa }}</td>
    </tr>
    <tr>
        <td align="left">{{ trim($h->texto2_consignat ?? '') }}</td>
        <td align="left">{{ $endereco }}</td>
    </tr>
    <tr>
        <td align="left">{{ trim($h->texto3_consignat ?? '') }}</td>
        <td align="left">{{ $municipio }} - {{ $uf }} - {{ $paisEmp }}</td>
    </tr>
    <tr>
        <td align="left">{{ trim($h->texto4_consignat ?? '') }}</td>
        <td align="left">CNPJ: {{ trim($h->num_cgc ?? '') }}</td>
    </tr>
    <tr>
        <td align="left">{{ trim($h->texto5_consignat ?? '') }}</td>
        <td align="left"></td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 4: SHIPPING DETAILS
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    @if($numBL)
    <tr>
        <th width="25%" align="left">B/L NUMBER:</th>
        <td width="75%" align="left">{{ $numBL }}</td>
    </tr>
    @endif
    <tr>
        <th align="left">DATED:</th>
        <td align="left">{{ $datEmbarque }}</td>
    </tr>
    <tr>
        <th align="left">VESSEL'S NAME:</th>
        <td align="left">{{ trim($h->den_navio_aviao ?? '') }}</td>
    </tr>
    <tr>
        <th align="left">CONTAINER NUMBER:</th>
        <td align="left">{{ trim($h->cod_container ?? '') }}</td>
    </tr>
    <tr>
        <th align="left">SEAL NR (CNTR):</th>
        <td align="left">{{ trim($h->cod_lacre ?? '') }}</td>
    </tr>
    <tr>
        <th align="left">SEAL NR (SIF):</th>
        <td align="left">{{ trim($h->cod_lacre_sif ?? '') }}</td>
    </tr>
    <tr>
        <th align="left">PORT OF LOADING:</th>
        <td align="left">{{ trim($h->local_embarque ?? '') }} - {{ $paisInt }}</td>
    </tr>
    <tr>
        <th align="left">FINAL PORT OF DISCHARGE:</th>
        <td align="left">{{ trim($h->local_destino ?? '') }}</td>
    </tr>
    <tr>
        <th align="left">FINAL DESTINATION:</th>
        <td align="left">{{ $paisDest }}</td>
    </tr>
    <tr>
        <th align="left">MARKS:</th>
        <td align="left">{{ trim($h->marca ?? '') }}</td>
    </tr>
    <tr>
        <th align="left">SHIPPING MARKS:</th>
        <td align="left">{{ trim($h->marca ?? '') }}</td>
    </tr>
    @if(trim($h->ordem ?? ''))
    <tr>
        <th align="left">PO:</th>
        <td align="left">{{ trim($h->ordem) }}</td>
    </tr>
    @endif
    @if($prodDatesStr)
    <tr>
        <th align="left">PRODUCTION DATE OF GOODS:</th>
        <td align="left">{{ $prodDatesStr }}</td>
    </tr>
    @endif
    @if($contTemp)
    <tr>
        <th align="left">STORAGE CONDITION:</th>
        <td align="left">GOODS SHIPPED UNDER MINUS <b>{{ $contTemp }}</b> CELSIUS DEGREES</td>
    </tr>
    @endif
    @if($ncm)
    <tr>
        <th align="left">NCM CODE:</th>
        <td align="left">{{ $ncm }}</td>
    </tr>
    @endif
    <tr><td style="height: 5px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 5: ITEMS TABLE (WEIGHT GRID)
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <th class="th-header b-t b-b b-l">NUMBER OF BLOCKS</th>
        <th class="th-header b-t b-b b-l">TOTAL NET WEIGHT</th>
        <th class="th-header b-t b-b b-l">TOTAL GROSS WEIGHT</th>
        <th class="th-header b-t b-b b-l">NET WEIGHT PER BLOCK</th>
        <th class="th-header b-t b-b b-l">GROSS WEIGHT PER BLOCK</th>
        <th align="left" class="th-header b-t b-b b-l b-r">DESCRIPTION OF PRODUCT</th>
    </tr>
    @foreach($items as $item)
    <tr>
        <th align="center" class="b-b b-l">{{ number_format((float)($item->tot_embalag ?? 0), 0, '.', ',') }}</th>
        <th align="center" class="b-b b-l">{{ number_format((float)($item->qtd_pecas_solic ?? 0), 3, '.', ',') }}</th>
        <th align="center" class="b-b b-l">{{ number_format((float)($item->rateio_palete ?? 0), 3, '.', ',') }}</th>
        <th align="center" class="b-b b-l">{{ number_format((float)($item->qtd_padr_embal ?? 0), 2, '.', ',') }}</th>
        <th align="center" class="b-b b-l">{{ number_format((float)($item->net_weight_block ?? 0), 2, '.', ',') }}</th>
        <th align="left" class="b-b b-l b-r">{{ trim($item->den_item_int ?? '') }}</th>
    </tr>
    @endforeach
    <tr><td style="height: 10px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 6: TOTALS
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <td width="23%" align="left" class="b-t b-b b-l">TOTAL CARTONS:</td>
        <th width="13%" align="right" class="b-t b-b b-l b-r">{{ number_format($totQtdCaixa, 0, '.', ',') }}</th>
        <td width="64%"></td>
    </tr>
    <tr>
        <td align="left" class="b-b b-l">TOTAL NET WEIGHT (KG):</td>
        <th align="right" class="b-b b-l b-r">{{ number_format($totPesoLiq, 3, '.', ',') }}</th>
        <td></td>
    </tr>
    <tr>
        <td align="left" class="b-b b-l">TOTAL GROSS WEIGHT (KG):</td>
        <th align="right" class="b-b b-l b-r">{{ number_format($totPesoBrt, 3, '.', ',') }}</th>
        <td></td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 7: CERTIFICATION TEXT
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <td width="20%"></td>
        <td width="60%" align="center">
            We hereby certify that the weight of goods informed above exported by company
            <b>{{ $empresa }}</b> - {{ $endereco }} - {{ $municipio }} - {{ $uf }} - {{ $paisEmp }},
            are true and corret.
        </td>
        <td width="20%"></td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 8: CITY + DATE
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <td width="30%" align="left">{{ $municipio }} - {{ $uf }} - {{ $paisEmp }}</td>
        <td width="40%" align="center">{{ $datAtualiz }}</td>
        <td width="30%"></td>
    </tr>
    <tr>
        <td style="height: 50px;"></td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 9: SIGNATURE
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

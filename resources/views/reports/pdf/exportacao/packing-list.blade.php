@php
    use Carbon\Carbon;

    // --- Branding ---
    $bg   = $branding['bg_color'];
    $fc   = $branding['font_color'];
    $logo = public_path($branding['logo']);
    $ass  = public_path($branding['signature']);

    $h = $header;

    // --- Variant Detection ---
    $paisDestino = strtoupper(trim($h->pais_destino ?? ''));
    $codEmpresa  = trim($h->cod_empresa ?? '');

    $isUSA     = ($paisDestino === 'UNITED STATES OF AMERICA');
    $spanishCountries = ['ARGENTINA', 'CHILE', 'URUGUAY', 'COLOMBIA', 'ESPANHA'];
    $isSpanish = in_array($paisDestino, $spanishCountries);

    $isSouthAfrica = in_array($paisDestino, ['SOUTH AFRICA', 'NAMIBIA']);
    $isKuwait      = ($paisDestino === 'KUWAIT');
    $isMexico      = ($paisDestino === 'MEXICO');
    $showLots      = ($isSouthAfrica || $isMexico || $isKuwait);

    $isBuyerOverride = (trim($h->cod_consignat ?? '') == '94955');

    // --- Title & Footer ---
    $docTitle = $isSpanish ? 'LISTA DE EMPAQUE' : 'PACKING LIST';
    $deptName = $isSpanish ? 'DEPARTAMENTO DE EXPORTACIÓN' : 'EXPORT DEPARTMENT';

    // --- Date Formatting ---
    $datEmbarque = $h->dat_embarque ? Carbon::parse($h->dat_embarque) : null;

    if ($isSpanish && $datEmbarque) {
        $mesesES = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                    7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
        $datEmbarqueFormatted = $datEmbarque->day . ' de ' . $mesesES[$datEmbarque->month] . ' de ' . $datEmbarque->year;
    } elseif ($datEmbarque) {
        $datEmbarqueFormatted = $datEmbarque->locale('en')->isoFormat('MMMM D, YYYY');
    } else {
        $datEmbarqueFormatted = now()->locale('en')->isoFormat('MMMM D, YYYY');
    }
    $dataAtual = $datEmbarqueFormatted;

    // --- Copy/Original Stamp ---
    $stampPath = null;
    if (($copyType ?? null) === 'original') {
        $stampPath = public_path('img/stamp-original.png');
    } elseif (($copyType ?? null) === 'copia') {
        $stampPath = public_path('img/stamp-copy.png');
    }

    // --- Summary Totals ---
    $totEmbalag   = 0;
    $totPesoLiq   = 0;
    $totPesoBruto = 0;
    $totQtdoPalete = 0;

    foreach ($summaryItems as $item) {
        $totEmbalag   += $item->tot_embalag ?? 0;
        $totPesoLiq   += $item->qtd_pecas_solic ?? 0;
        $totPesoBruto += $item->rateio_palete ?? 0;
        if ($isSpanish) {
            $totQtdoPalete += $item->qtdo_palete ?? 0;
        }
    }

    $tipEmbal = trim($h->tip_embal ?? 'CARTONS');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Packing List {{ trim($h->proforma) }}-{{ trim($h->embarque) }}</title>
    <style>
        @page { margin: 30px 25px 20px 25px; }

        * { font-family: Helvetica, Arial, sans-serif; font-size: 10.5px; line-height: 1.3; }

        body { margin: 0; padding: 0; color: #333; }

        table { border-collapse: separate; border-spacing: 0; width: 100%; }

        th, td { vertical-align: top; padding: 2px 4px; font-size: 10.5px; }

        .b-all  { border: 1px solid #000; }
        .b-t    { border-top: 1px solid #000; }
        .b-b    { border-bottom: 1px solid #000; }
        .b-l    { border-left: 1px solid #000; }
        .b-r    { border-right: 1px solid #000; }

        .th-header {
            background: {{ $bg }};
            color: {{ $fc }};
            font-weight: bold;
            padding: 4px 6px;
            text-align: center;
        }

        .td-val {
            padding: 3px 5px;
            vertical-align: top;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }

        .item-header {
            font-weight: bold;
            text-align: center;
            padding: 4px 6px;
        }
    </style>
</head>
<body>

{{-- ══════════════════════════════════════════════════════════════════
     SECTION 1 — COMPANY HEADER (Logo + Marca + Stamp + Logo)
     ══════════════════════════════════════════════════════════════════ --}}
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
        <td align="center">{{ trim($h->den_razao_social ?? '') }}</td>
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

{{-- ══════════════════════════════════════════════════════════════════
     SECTION 2 — TITLE
     ══════════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="2">
    <tr>
        <th align="center" style="font-size: 16px; font-weight: bold;">{{ $docTitle }}</th>
    </tr>
    <tr>
        <th align="center">{{ trim($h->proforma ?? '') }}-{{ trim($h->embarque ?? '') }}</th>
    </tr>
    <tr>
        <td class="b-b"></td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════════
     SECTION 3 — CONSIGNEE / EXPORTER / SHIPPING DETAILS
     ══════════════════════════════════════════════════════════════════ --}}

@if($isSpanish)
    {{-- ── SPANISH VARIANT ── --}}
    <table width="100%" cellspacing="0" cellpadding="3">
        <tr><th colspan="3" align="right">{{ $dataAtual }}</th></tr>
        <tr><th colspan="3" align="left">CONSIGNATARIO / CONSIGNEE:</th></tr>
        <tr><td colspan="3">{{ trim($h->texto1_consignat ?? '') }}</td></tr>
        <tr><td colspan="3">{{ trim($h->texto2_consignat ?? '') }}</td></tr>
        <tr><td colspan="3">{{ trim($h->texto3_consignat ?? '') }}</td></tr>
        <tr><td colspan="3">{{ trim($h->texto4_consignat ?? '') }}</td></tr>
        <tr><td colspan="3">{{ trim($h->texto5_consignat ?? '') }}</td></tr>
        <tr><td colspan="3" style="height:5px;"></td></tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="3">
        <tr>
            <th width="35%" align="left">DE:</th>
            <td colspan="2">{{ trim($h->den_munic ?? '') }}- {{ trim($h->den_uni_feder ?? '') }}- {{ trim($h->pais_emp ?? '') }}</td>
        </tr>
        <tr><th align="left">PARA:</th><td colspan="2">{{ trim($h->local_destino ?? '') }}</td></tr>
        <tr><th align="left">MARCACIÓN:</th><td colspan="2">{{ trim($h->marca ?? '') }}</td></tr>
        <tr><th align="left">TRANSPORTISTA:</th><td colspan="2">{{ trim($h->transportadora ?? '') }}</td></tr>
        <tr><th align="left">TERMO DEL PAGAMENTO:</th><td>{{ trim($h->cond_pgto_ingles ?? '') }}</td></tr>
        <tr><th align="left">TERMO DE LA ENTREGA:</th><td>{{ trim($h->local_destino ?? '') }}</td></tr>
        <tr><th align="left">EMBALAJE:</th><td>{{ trim($h->embal_invoice ?? '') }}</td></tr>
        <tr><th align="left">EMBARCADO EN:</th><td>{{ $datEmbarqueFormatted }}</td></tr>
        <tr><th align="left">ORDEN DE COMPRA:</th><td>{{ trim($h->ordem ?? '') }}</td></tr>
        <tr><td style="height:5px;"></td></tr>
    </table>

@elseif($isUSA)
    {{-- ── USA VARIANT ── --}}
    <table width="100%" cellspacing="0" cellpadding="3">
        <tr><th colspan="2" align="right">{{ $dataAtual }}</th></tr>
        <tr>
            <th width="50%" align="left">CONSIGNEE:</th>
            <th width="50%" align="left">EXPORTED BY:</th>
        </tr>
        <tr>
            <td>{{ trim($h->texto1_consignat ?? '') }}</td>
            <td>{{ trim($h->den_razao_social ?? '') }}</td>
        </tr>
        <tr>
            <td>{{ trim($h->texto2_consignat ?? '') }}</td>
            <td>{{ trim($h->end_empresa ?? '') }}</td>
        </tr>
        <tr>
            <td>{{ trim($h->texto3_consignat ?? '') }}</td>
            <td>{{ trim($h->den_munic ?? '') }}- {{ trim($h->den_uni_feder ?? '') }}- {{ trim($h->pais_emp ?? '') }}</td>
        </tr>
        <tr>
            <td>{{ trim($h->texto4_consignat ?? '') }}</td>
            <td>CNPJ: {{ trim($h->num_cgc ?? '') }}</td>
        </tr>
        <tr>
            <td>{{ trim($h->texto5_consignat ?? '') }}</td>
            <td>CEP: {{ trim($h->cod_cep ?? '') }}</td>
        </tr>
        <tr><td></td><td>TEL.: {{ trim($h->num_telefone ?? '') }} - FAX: {{ trim($h->num_fax ?? '') }}</td></tr>
        <tr><td></td><td>WEBSITE: {{ trim($h->site ?? '') }}</td></tr>
        <tr><td></td><td>E-MAIL: {{ trim($h->email_contato ?? '') }}</td></tr>
        <tr><td colspan="2" style="height:5px;"></td></tr>
    </table>

    {{-- Shipping Details --}}
    <table width="100%" cellspacing="0" cellpadding="3">
        <tr><th width="30%" align="left">B/L NUMBER:</th><td>{{ trim($h->num_bl ?? '') }}</td></tr>
        <tr><th align="left">DATED:</th><td>{{ $datEmbarqueFormatted }}</td></tr>
        <tr><th align="left">VESSEL NAME:</th><td>{{ trim($h->den_navio_aviao ?? '') }}</td></tr>
        <tr><th align="left">CONTAINER NUMBER:</th><td>{{ trim($h->cod_container ?? '') }}</td></tr>
        <tr><th align="left">SEAL NR (CNTR):</th><td>{{ trim($h->cod_lacre ?? '') }}</td></tr>
        <tr><th align="left">SEAL NR (SIF):</th><td>{{ trim($h->cod_lacre_sif ?? '') }}</td></tr>
        <tr><th align="left">PORT OF LOADING:</th><td>{{ trim($h->local_embarque ?? '') }}- {{ trim($h->pais_int ?? '') }}</td></tr>
        <tr><th align="left">FINAL PORT OF DISCHARGE:</th><td>{{ trim($h->local_destino ?? '') }}</td></tr>
        <tr><th align="left">FINAL DESTINATION:</th><td>{{ $paisDestino }}</td></tr>
        <tr><th align="left">MARKS:</th><td>{{ trim($h->marca ?? '') }}</td></tr>
        <tr><th align="left">NCM:</th><td>{{ $ncm }}</td></tr>
        <tr><th align="left">PO:</th><td>{{ trim($h->ordem ?? '') }}</td></tr>
        @if(trim($h->ies_termografo ?? ''))
            <tr><th align="left">TERMOGRAFO:</th><td>{{ trim($h->ies_termografo) }}</td></tr>
        @endif
        <tr><td style="height:5px;"></td></tr>
    </table>

@else
    {{-- ── DEFAULT VARIANT ── --}}
    <table width="100%" cellspacing="0" cellpadding="3">
        <tr><th colspan="2" align="right">{{ $dataAtual }}</th></tr>
        <tr>
            <th width="50%" align="left">CONSIGNEE:</th>
            <th width="50%" align="left">EXPORTED BY:</th>
        </tr>
        @if($isBuyerOverride)
            <tr>
                <td>{{ trim($h->texto1_buyer ?? '') }}</td>
                <td>{{ trim($h->den_razao_social ?? '') }}</td>
            </tr>
            <tr>
                <td>{{ trim($h->texto5_buyer ?? '') }} {{ trim($h->tip_logradouro ?? '') }} {{ trim($h->texto3_buyer ?? '') }}</td>
                <td>{{ trim($h->end_empresa ?? '') }}</td>
            </tr>
            <tr>
                <td>{{ trim($h->texto4_buyer ?? '') }} {{ trim($h->texto2_buyer ?? '') }}</td>
                <td>{{ trim($h->den_munic ?? '') }}- {{ trim($h->den_uni_feder ?? '') }}- {{ trim($h->pais_emp ?? '') }}</td>
            </tr>
            <tr>
                <td>{{ trim($h->den_cidade ?? '') }}</td>
                <td>CNPJ: {{ trim($h->num_cgc ?? '') }}</td>
            </tr>
            <tr><td></td><td>WEBSITE: {{ trim($h->site ?? '') }}</td></tr>
            <tr><td></td><td>E-MAIL: {{ trim($h->email_contato ?? '') }}</td></tr>
        @else
            <tr>
                <td>{{ trim($h->texto1_consignat ?? '') }}</td>
                <td>{{ trim($h->den_razao_social ?? '') }}</td>
            </tr>
            <tr>
                <td>{{ trim($h->texto2_consignat ?? '') }}</td>
                <td>{{ trim($h->end_empresa ?? '') }}</td>
            </tr>
            <tr>
                <td>{{ trim($h->texto3_consignat ?? '') }}</td>
                <td>{{ trim($h->den_munic ?? '') }}- {{ trim($h->den_uni_feder ?? '') }}- {{ trim($h->pais_emp ?? '') }}</td>
            </tr>
            <tr>
                <td>{{ trim($h->texto4_consignat ?? '') }}</td>
                <td>CNPJ: {{ trim($h->num_cgc ?? '') }}</td>
            </tr>
            <tr>
                <td>{{ trim($h->texto5_consignat ?? '') }}</td>
                <td></td>
            </tr>
            <tr><td></td><td>WEBSITE: {{ trim($h->site ?? '') }}</td></tr>
            <tr><td></td><td>E-MAIL: {{ trim($h->email_contato ?? '') }}</td></tr>
        @endif
        <tr><td colspan="2" style="height:5px;"></td></tr>
    </table>

    {{-- Shipping Details --}}
    <table width="100%" cellspacing="0" cellpadding="3">
        <tr><th width="30%" align="left">B/L NUMBER:</th><td>{{ trim($h->num_bl ?? '') }}</td></tr>
        <tr><th align="left">DATED:</th><td>{{ $datEmbarqueFormatted }}</td></tr>
        <tr><th align="left">VESSEL NAME:</th><td>{{ trim($h->den_navio_aviao ?? '') }}</td></tr>
        <tr><th align="left">CONTAINER NUMBER:</th><td>{{ trim($h->cod_container ?? '') }}</td></tr>
        <tr><th align="left">SEAL NR (CNTR):</th><td>{{ trim($h->cod_lacre ?? '') }}</td></tr>
        <tr><th align="left">SEAL NR (SIF):</th><td>{{ trim($h->cod_lacre_sif ?? '') }}</td></tr>
        <tr><th align="left">PORT OF LOADING:</th><td>{{ trim($h->local_embarque ?? '') }}- {{ trim($h->pais_int ?? '') }}</td></tr>
        <tr><th align="left">FINAL PORT OF DISCHARGE:</th><td>{{ trim($h->local_destino ?? '') }}</td></tr>
        <tr><th align="left">FINAL DESTINATION:</th><td>{{ $paisDestino }}</td></tr>
        @if($isSouthAfrica && trim($h->import_permit ?? ''))
            <tr><th align="left">PERMIT:</th><td>{{ trim($h->import_permit) }}</td></tr>
        @endif
        @if($isSouthAfrica)
            <tr><th align="left">HC:</th><td>{{ trim($h->ies_csi_dsc ?? '') }}</td></tr>
        @endif
        <tr><th align="left">MARKS:</th><td>{{ trim($h->marca ?? '') }}</td></tr>
        <tr><th align="left">NCM:</th><td>{{ $ncm }}</td></tr>
        <tr><th align="left">PO:</th><td>{{ trim($h->ordem ?? '') }}</td></tr>
        @if(trim($h->ies_termografo ?? ''))
            <tr><th align="left">TERMOGRAFO:</th><td>{{ trim($h->ies_termografo) }}</td></tr>
        @endif
        <tr><td style="height:5px;"></td></tr>
    </table>
@endif

{{-- ══════════════════════════════════════════════════════════════════
     SECTION 4 — SUMMARY ITEMS TABLE
     ══════════════════════════════════════════════════════════════════ --}}

@if($isSpanish)
    <table width="100%" cellspacing="0" cellpadding="3">
        <tr>
            <th class="th-header b-t b-b b-l" style="width:15%;">CANTIDAD PALLETS DE MADERA</th>
            <th class="th-header b-t b-b b-l" style="width:12%;">CANTIDAD DE CAJAS</th>
            <th class="th-header b-t b-b b-l" style="width:12%;">PESO NETO (KG)</th>
            <th class="th-header b-t b-b b-l" style="width:12%;">PESO BRUTO (KG)</th>
            <th class="th-header b-t b-b b-l b-r" style="width:49%;">DESCRIPCIÓN DEL LA MERCANCIA</th>
        </tr>
        @php $lastQtdoPalete = 0; $lastRateio = 0; $lastQtdCaixa = 0; @endphp
        @foreach($summaryItems as $item)
            @php
                $lastQtdoPalete = $item->qtdo_palete ?? 0;
                $lastQtdCaixa   = number_format($item->tot_embalag ?? 0, 0, '.', ',');
                $pesoLiq        = number_format($item->qtd_pecas_solic ?? 0, 3, '.', ',');
                $lastRateio     = number_format($item->rateio_palete ?? 0, 3, '.', ',');
            @endphp
            <tr>
                <td class="td-val b-b b-l text-center font-bold">{{ $lastQtdoPalete }}</td>
                <td class="td-val b-b b-l text-center font-bold">{{ $lastQtdCaixa }}</td>
                <td class="td-val b-b b-l text-center font-bold">{{ $pesoLiq }}</td>
                <td class="td-val b-b b-l text-center font-bold">{{ $lastRateio }}</td>
                <td class="td-val b-b b-l b-r text-left font-bold">{{ trim($item->den_item ?? '') }}<br>{{ trim($item->den_item_int ?? '') }}</td>
            </tr>
        @endforeach
        @if(trim($h->ies_termografo ?? ''))
            <tr>
                <td class="td-val b-b b-l"></td>
                <td class="td-val b-b"></td>
                <td class="td-val b-b"></td>
                <td class="td-val b-b"></td>
                <td class="td-val b-b b-l b-r text-center">*Con Termografo</td>
            </tr>
        @endif
    </table>

    <table cellspacing="0" cellpadding="0"><tr><td style="height: 5px;"></td></tr></table>
    <table cellspacing="0" cellpadding="3">
        <tr>
            <td width="23%" align="left" class="b-t b-b b-l">TOTAL PALETES DE MADERA:</td>
            <th width="13%" align="right" class="b-t b-b b-l b-r">{{ $lastQtdoPalete }}</th>
            <td width="64%"></td>
        </tr>
        <tr>
            <td align="left" class="b-b b-l">TOTAL CAJAS DE CARTÓN:</td>
            <th align="right" class="b-b b-l b-r">{{ number_format($totEmbalag, 0, '.', ',') }}</th>
            <td></td>
        </tr>
        <tr>
            <td align="left" class="b-b b-l">TOTAL PESO NETO (KG):</td>
            <th align="right" class="b-b b-l b-r">{{ number_format($totPesoLiq, 3, '.', ',') }}</th>
            <td></td>
        </tr>
        <tr>
            <td align="left" class="b-b b-l">TOTAL PESO BRUTO (KG):</td>
            <th align="right" class="b-b b-l b-r">{{ number_format($totPesoBruto, 3, '.', ',') }}</th>
            <td></td>
        </tr>
    </table>

@elseif($isUSA)
    <table width="100%" cellspacing="0" cellpadding="3">
        <tr>
            <th class="th-header b-t b-b b-l" style="width:15%;">NUMBER OF {{ $tipEmbal }}</th>
            <th class="th-header b-t b-b b-l" style="width:20%;">TOTAL NET WEIGHT</th>
            <th class="th-header b-t b-b b-l" style="width:20%;">TOTAL GROSS WEIGHT</th>
            <th class="th-header b-t b-b b-l b-r" style="width:45%;">DESCRIPTION OF PRODUCT</th>
        </tr>
        @foreach($summaryItems as $item)
            <tr>
                <td class="td-val b-b b-l text-center font-bold">{{ number_format($item->tot_embalag ?? 0, 3, '.', ',') }}</td>
                <td class="td-val b-b b-l text-center font-bold">{{ number_format($item->qtd_pecas_solic ?? 0, 0, '.', ',') }}</td>
                <td class="td-val b-b b-l text-center font-bold">{{ number_format($item->rateio_palete ?? 0, 2, '.', ',') }}</td>
                <td class="td-val b-b b-l b-r text-left">{{ trim($item->den_item_int ?? '') }}</td>
            </tr>
        @endforeach
    </table>

    <table cellspacing="0" cellpadding="0"><tr><td style="height: 5px;"></td></tr></table>
    <table cellspacing="0" cellpadding="3">
        <tr>
            <td width="23%" align="left" class="b-t b-b b-l">TOTAL {{ $tipEmbal }}:</td>
            <th width="13%" align="right" class="b-t b-b b-l b-r">{{ number_format($totEmbalag, 0, '.', ',') }}</th>
            <td width="64%"></td>
        </tr>
        <tr>
            <td align="left" class="b-b b-l">TOTAL NET WEIGHT (KG):</td>
            <th align="right" class="b-b b-l b-r">{{ number_format($totPesoLiq, 3, '.', ',') }}</th>
            <td></td>
        </tr>
        <tr>
            <td align="left" class="b-b b-l">TOTAL GROSS WEIGHT (KG):</td>
            <th align="right" class="b-b b-l b-r">{{ number_format($totPesoBruto, 3, '.', ',') }}</th>
            <td></td>
        </tr>
    </table>

@else
    {{-- DEFAULT VARIANT — 6 columns --}}
    <table width="100%" cellspacing="0" cellpadding="3">
        <tr>
            <th class="th-header b-t b-b b-l" style="width:13%;">NUMBER OF {{ $tipEmbal }}</th>
            <th class="th-header b-t b-b b-l" style="width:13%;">TOTAL NET WEIGHT</th>
            <th class="th-header b-t b-b b-l" style="width:13%;">TOTAL GROSS WEIGHT</th>
            <th class="th-header b-t b-b b-l" style="width:13%;">NET WEIGHT PER BLOCK</th>
            <th class="th-header b-t b-b b-l" style="width:13%;">GROSS WEIGHT PER BLOCK</th>
            <th class="th-header b-t b-b b-l b-r" style="width:35%;">DESCRIPTION OF PRODUCT</th>
        </tr>
        @foreach($summaryItems as $item)
            <tr>
                <td class="td-val b-b b-l text-center font-bold">{{ number_format($item->tot_embalag ?? 0, 0, '.', ',') }}</td>
                <td class="td-val b-b b-l text-center font-bold">{{ number_format($item->qtd_pecas_solic ?? 0, 3, '.', ',') }}</td>
                <td class="td-val b-b b-l text-center font-bold">{{ number_format($item->rateio_palete ?? 0, 3, '.', ',') }}</td>
                <td class="td-val b-b b-l text-center font-bold">{{ number_format($item->qtd_padr_embal ?? 0, 2, '.', ',') }}</td>
                <td class="td-val b-b b-l text-center font-bold">{{ number_format($item->qtd_bruto ?? 0, 2, '.', ',') }}</td>
                <td class="td-val b-b b-l b-r text-left">{{ trim($item->den_item_int ?? '') }}</td>
            </tr>
        @endforeach
    </table>

    {{-- texto_docs (conditional) --}}
    @if(trim($h->texto_docs1 ?? '') || trim($h->texto_docs2 ?? '') || trim($h->texto_docs3 ?? ''))
        <table width="100%" cellspacing="0" cellpadding="3">
            @if(trim($h->texto_docs1 ?? ''))
                <tr><th class="td-val b-b b-l b-r text-left">{{ trim($h->texto_docs1) }}</th></tr>
            @endif
            @if(trim($h->texto_docs2 ?? ''))
                <tr><th class="td-val b-b b-l b-r text-left">{{ trim($h->texto_docs2) }}</th></tr>
            @endif
            @if(trim($h->texto_docs3 ?? ''))
                <tr><th class="td-val b-b b-l b-r text-left">{{ trim($h->texto_docs3) }}</th></tr>
            @endif
        </table>
    @endif

    <table cellspacing="0" cellpadding="0"><tr><td style="height: 5px;"></td></tr></table>
    <table cellspacing="0" cellpadding="3">
        <tr>
            <td width="23%" align="left" class="b-t b-b b-l">TOTAL {{ $tipEmbal }}:</td>
            <th width="13%" align="right" class="b-t b-b b-l b-r">{{ number_format($totEmbalag, 0, '.', ',') }}</th>
            <td width="64%"></td>
        </tr>
        <tr>
            <td align="left" class="b-b b-l">TOTAL NET WEIGHT (KG):</td>
            <th align="right" class="b-b b-l b-r">{{ number_format($totPesoLiq, 3, '.', ',') }}</th>
            <td></td>
        </tr>
        <tr>
            <td align="left" class="b-b b-l">TOTAL GROSS WEIGHT (KG):</td>
            <th align="right" class="b-b b-l b-r">{{ number_format($totPesoBruto, 3, '.', ',') }}</th>
            <td></td>
        </tr>
    </table>
@endif

{{-- ══════════════════════════════════════════════════════════════════
     SECTION 5 — PACKING DETAIL (per item, production dates)
     ══════════════════════════════════════════════════════════════════ --}}

@if(!empty($packingDetail))
    <table cellspacing="0" cellpadding="0"><tr><td style="height: 5px;"></td></tr></table>
    @foreach($packingDetail as $codItem => $rows)
        @php
            // Find the den_item_int from summary items
            $itemDesc = '';
            foreach ($summaryItems as $si) {
                if (trim($si->cod_item ?? '') === $codItem || trim($si->den_item_int ?? '') !== '') {
                    $itemDesc = trim($si->den_item_int ?? '');
                }
            }

            $detTotCartons    = 0;
            $detTotNetWeight  = 0;
            $detTotGrossWeight = 0;
        @endphp

        <table cellspacing="0" cellpadding="3" style="width: 60%; margin-bottom: 5px;">
            {{-- Item header --}}
            <tr>
                @if($isSpanish)
                    <th colspan="4" class="item-header b-t b-l b-r">{{ $codItem }} - {{ $itemDesc }}</th>
                @elseif($isUSA)
                    <th colspan="5" class="item-header b-t b-l b-r">{{ $codItem }} - {{ $itemDesc }}</th>
                @elseif($showLots)
                    <th colspan="5" class="item-header b-t b-l b-r">{{ $codItem }} - {{ $itemDesc }}</th>
                @else
                    <th colspan="4" class="item-header b-t b-l b-r">{{ $codItem }} - {{ $itemDesc }}</th>
                @endif
            </tr>

            {{-- Column headers --}}
            @if($isSpanish)
                <tr>
                    <th class="th-header b-t b-b b-l" style="width:30%;">FECHA DE PRODUCCIÓN</th>
                    <th class="th-header b-t b-b b-l" style="width:30%;">FECHA DE VENCIMIENTO</th>
                    <th class="th-header b-t b-b b-l" style="width:20%;">CAJAS</th>
                    <th class="th-header b-t b-b b-l b-r" style="width:25%;">PESO NETO</th>
                </tr>
            @elseif($isUSA)
                <tr>
                    <th class="th-header b-t b-b b-l" style="width:30%;">PRODUCTION DATE</th>
                    <th class="th-header b-t b-b b-l" style="width:20%;">EXPIRY DATE</th>
                    <th class="th-header b-t b-b b-l" style="width:15%;">{{ $tipEmbal }}</th>
                    <th class="th-header b-t b-b b-l b-r" style="width:20%;">NET WEIGHT (KG)</th>
                    <th class="th-header b-t b-b b-r" style="width:20%;">GROSS WEIGHT (KG)</th>
                </tr>
            @elseif($showLots)
                <tr>
                    <th class="th-header b-t b-b b-l" style="width:30%;">PRODUCTION DATE</th>
                    <th class="th-header b-t b-b b-l" style="width:20%;">EXPIRY DATE</th>
                    <th class="th-header b-t b-b b-l" style="width:15%;">{{ $tipEmbal }}</th>
                    <th class="th-header b-t b-b b-l b-r" style="width:20%;">NET WEIGHT</th>
                    <th class="th-header b-t b-b b-r" style="width:20%;">LOTS</th>
                </tr>
            @else
                <tr>
                    <th class="th-header b-t b-b b-l" style="width:30%;">PRODUCTION DATE</th>
                    <th class="th-header b-t b-b b-l" style="width:30%;">EXPIRY DATE</th>
                    <th class="th-header b-t b-b b-l" style="width:20%;">{{ $tipEmbal }}</th>
                    <th class="th-header b-t b-b b-l b-r" style="width:25%;">NET WEIGHT</th>
                </tr>
            @endif

            {{-- Detail rows --}}
            @foreach($rows as $row)
                @php
                    $detTotCartons     += $row->cartons;
                    $detTotNetWeight   += $row->net_weight;
                    $detTotGrossWeight += $row->gross_weight;
                @endphp

                @if($isSpanish)
                    <tr>
                        <td class="td-val b-b b-l text-center">{{ $row->production_date }}</td>
                        <td class="td-val b-b b-l text-center">{{ $row->expiry_date }}</td>
                        <td class="td-val b-b b-l text-right">{{ number_format($row->cartons, 0, ',', '.') }}</td>
                        <td class="td-val b-b b-l b-r text-right">{{ number_format($row->net_weight, 3, ',', '.') }}</td>
                    </tr>
                @elseif($isUSA)
                    <tr>
                        <td class="td-val b-b b-l text-center">{{ $row->production_date }}</td>
                        <td class="td-val b-b b-l text-center">{{ $row->expiry_date }}</td>
                        <td class="td-val b-b b-l text-center">{{ number_format($row->cartons, 0, ',', '.') }}</td>
                        <td class="td-val b-b b-l b-r text-right">{{ number_format($row->net_weight, 3, ',', '.') }}</td>
                        <td class="td-val b-b b-r text-right">{{ number_format($row->gross_weight, 3, ',', '.') }}</td>
                    </tr>
                @elseif($showLots)
                    <tr>
                        <td class="td-val b-b b-l text-center">{{ $row->production_date }}</td>
                        <td class="td-val b-b b-l text-center">{{ $row->expiry_date }}</td>
                        <td class="td-val b-b b-l text-right">{{ number_format($row->cartons, 2, '.', ',') }}</td>
                        <td class="td-val b-b b-l b-r text-right">{{ number_format($row->net_weight, 2, '.', ',') }}</td>
                        <td class="td-val b-b b-r text-center">{{ $row->lots }}</td>
                    </tr>
                @else
                    <tr>
                        <td class="td-val b-b b-l text-center">{{ $row->production_date }}</td>
                        <td class="td-val b-b b-l text-center">{{ $row->expiry_date }}</td>
                        <td class="td-val b-b b-l text-right">{{ number_format($row->cartons, 0, ',', '.') }}</td>
                        <td class="td-val b-b b-l b-r text-right">{{ number_format($row->net_weight, 3, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach

            {{-- Item totals --}}
            @if($isSpanish)
                <tr>
                    <th class="td-val"></th>
                    <th class="td-val b-b b-l text-right">TOTAL:</th>
                    <td class="td-val b-b b-l text-right">{{ number_format($detTotCartons, 2, '.', ',') }}</td>
                    <td class="td-val b-b b-l b-r text-right">{{ number_format($detTotNetWeight, 3, '.', ',') }}</td>
                </tr>
            @elseif($isUSA)
                <tr>
                    <th class="td-val text-right" colspan="1">TOTAL:</th>
                    <td class="td-val b-b b-l text-center">{{ number_format($detTotCartons, 0, ',', '.') }}</td>
                    <td class="td-val b-b b-l b-r text-right">{{ number_format($detTotNetWeight, 3, ',', '.') }}</td>
                    <td class="td-val b-b b-r text-right">{{ number_format($detTotGrossWeight, 3, ',', '.') }}</td>
                </tr>
            @elseif($showLots)
                <tr>
                    <th class="td-val b-b b-l"></th>
                    <th class="td-val b-b b-l text-right">TOTAL:</th>
                    <td class="td-val b-b b-l text-right">{{ number_format($detTotCartons, 0, '.', ',') }}</td>
                    <td class="td-val b-b b-l b-r text-right">{{ number_format($detTotNetWeight, 3, '.', ',') }}</td>
                    <td class="td-val b-b b-r"></td>
                </tr>
            @else
                <tr>
                    <th class="td-val"></th>
                    <th class="td-val b-b b-l text-right">TOTAL:</th>
                    <td class="td-val b-b b-l text-right">{{ number_format($detTotCartons, 0, ',', '.') }}</td>
                    <td class="td-val b-b b-l b-r text-right">{{ number_format($detTotNetWeight, 3, ',', '.') }}</td>
                </tr>
            @endif
        </table>
    @endforeach
@endif

{{-- ══════════════════════════════════════════════════════════════════
     SECTION 6 — SIGNATURE + FOOTER
     ══════════════════════════════════════════════════════════════════ --}}
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

<table cellspacing="0" cellpadding="3">
    <tr>
        <td colspan="3" class="b-b"></td>
    </tr>
    <tr>
        <td width="25%"></td>
        <td width="50%" align="center"><b>{{ trim($h->den_razao_social ?? '') }}</b></td>
        <td width="25%"></td>
    </tr>
    <tr>
        <td></td>
        <td align="center">{{ $deptName }}</td>
        <td></td>
    </tr>
</table>

</body>
</html>

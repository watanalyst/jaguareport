@php
    use Carbon\Carbon;

    // --- Branding ---
    $bg   = $branding['bg_color'];
    $fc   = $branding['font_color'];
    $logo = public_path($branding['logo']);
    $sif  = public_path($branding['sif'] ?? '');
    $ass  = public_path($branding['signature']);

    $h = $header;

    // --- Variant Detection (Scriptcase: cod_emp == 16 && ARGENTINA) ---
    $paisDestino = strtoupper(trim($h->pais_destino ?? ''));
    $codEmpresa  = trim($h->cod_empresa ?? '');

    $isUSA       = ($paisDestino === 'UNITED STATES OF AMERICA');
    $isArgentina = ($codEmpresa == '16' && $paisDestino === 'ARGENTINA');

    // --- Title ---
    $docTitle = $isArgentina ? 'FACTURA' : 'INVOICE';
    $deptName = $isArgentina ? 'DEPARTAMENTO DE EXPORTACIÓN' : 'EXPORT DEPARTMENT';

    // --- Date Formatting ---
    $datEmbarque = $h->dat_embarque ? Carbon::parse($h->dat_embarque) : null;
    $datEmbarqueFormatted = $datEmbarque
        ? $datEmbarque->locale('en')->isoFormat('MMMM D, YYYY')
        : '';

    // --- Copy/Original Stamp ---
    $stampPath = null;
    if (($copyType ?? null) === 'original') {
        $stampPath = public_path('img/stamp-original.png');
    } elseif (($copyType ?? null) === 'copia') {
        $stampPath = public_path('img/stamp-copy.png');
    }

    // --- Items Totals ---
    $totEmbalag   = 0;
    $totPesoLiq   = 0;
    $totPesoBruto = 0;
    $totValItem   = 0;

    foreach ($items as $item) {
        $totEmbalag   += $item->tot_embalag ?? 0;
        $totPesoLiq   += $item->qtd_pecas_solic ?? 0;
        $totPesoBruto += $item->rateio_palete ?? 0;
        $totValItem   += $item->val_tot_item ?? 0;
    }

    // Peso bruto do header (campo RATEIO_PALETE da query de header, como no Scriptcase)
    $pesoBrutoHeader = $h->rateio_palete ?? $totPesoBruto;

    // Val frete do header (campo VAL_FRETE_EMBARQUE)
    $valFreteHeader = $h->val_frete_embarque ?? 0;

    $pctAdiant    = $h->pct_adiant ?? 0;
    $valRecebAdto = $h->val_receb_adto ?? 0;
    $valRestPagar = $totValItem - $valRecebAdto;
    $denMoeda     = trim($h->den_moeda_abrev ?? '');

    $isCAD = strtoupper(trim($h->obs_cnd_pgto ?? '')) === 'CAD';

    // Format helpers
    $fmtValRecebAdto = number_format($valRecebAdto, 2, '.', ',');
    $fmtValRestPagar = number_format($valRestPagar, 2, '.', ',');
    $fmtTotValItem   = number_format($totValItem, 2, '.', ',');
    $fmtValFrete     = number_format($valFreteHeader, 2, '.', ',');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice Frete {{ trim($h->proforma) }}-{{ trim($h->embarque) }}</title>
    <style>
        @page { margin: 30px 25px 20px 25px; }

        * { font-family: Helvetica, Arial, sans-serif; font-size: 10.5px; line-height: 1.3; }

        body { margin: 0; padding: 0; color: #333; }

        p { margin: 0; padding: 0; }

        table { border-collapse: separate; border-spacing: 0; width: 100%; }

        th, td { vertical-align: top; padding: 2px 4px; font-size: 10.5px; word-wrap: break-word; }

        .inv-title { font-size: 16px; font-weight: bold; }

        .b-all { border: 1px solid #000; }
        .b-t   { border-top: 1px solid #000; }
        .b-b   { border-bottom: 1px solid #000; }
        .b-l   { border-left: 1px solid #000; }
        .b-r   { border-right: 1px solid #000; }
    </style>
</head>
<body>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 1: COMPANY HEADER (Logo, Marca, Stamp)
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="2">
    <tr>
        <td width="15%" rowspan="4" align="center">
            @if(file_exists($logo))
                <img src="{{ $logo }}" alt="LOGO" width="100">
            @endif
        </td>
        <td width="13%" rowspan="4"></td>
        <th width="30%" align="center" style="font-size: 16px;">{{ trim($h->marca) }}</th>
        <td width="13%" rowspan="4" align="right">
            @if($stampPath && file_exists($stampPath))
                <img src="{{ $stampPath }}" alt="STAMP" width="120">
            @endif
        </td>
        <td width="15%" rowspan="4" align="center">
            @if(file_exists($logo))
                <img src="{{ $logo }}" alt="LOGO" width="100">
            @endif
        </td>
    </tr>
    <tr>
        <td align="center">{{ trim($h->den_razao_social) }}</td>
    </tr>
    <tr>
        <td align="center">CNPJ: {{ trim($h->num_cgc) }}</td>
    </tr>
    <tr>
        <td align="center">PHONE: {{ trim($h->num_telefone) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="b-b"></td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 2: TITLE + PROFORMA-EMBARQUE
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="2">
    <tr>
        <th align="center" class="inv-title">{{ $docTitle }}</th>
    </tr>
    <tr>
        <th align="center">{{ trim($h->proforma) }}-{{ trim($h->embarque) }}</th>
    </tr>
    <tr>
        <td class="b-b"></td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 3: BUYER / CONSIGNEE / EXPORTER
     ══════════════════════════════════════════════════════════════ --}}
@if ($isUSA)
{{-- USA VARIANT: 3 columns with full exporter details --}}
<table cellspacing="0" cellpadding="3" style="table-layout: fixed;">
    <tr>
        <th colspan="3" align="right">{{ $datEmbarqueFormatted }}</th>
    </tr>
    <tr>
        <th width="38%" align="left">COMPRADOR / BUYER:</th>
        <th width="32%" align="left">CONSIGNATÁRIO / CONSIGNEE</th>
        <th width="30%" align="left">EXPORTEDOR / EXPORTER:</th>
    </tr>
    <tr>
        <td>{{ trim($h->texto1_buyer) }}</td>
        <td>{{ trim($h->texto1_consignat) }}</td>
        <td>{{ trim($h->den_razao_social) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto2_buyer) }}</td>
        <td>{{ trim($h->texto2_consignat) }}</td>
        <td>CNPJ: {{ trim($h->num_cgc) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto3_buyer) }}</td>
        <td>{{ trim($h->texto3_consignat) }}</td>
        <td>{{ trim($h->end_empresa) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto4_buyer) }}</td>
        <td>{{ trim($h->texto4_consignat) }}</td>
        <td>CEP: {{ trim($h->cod_cep) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto5_buyer) }}</td>
        <td>{{ trim($h->texto5_consignat) }}</td>
        <td>{{ trim($h->den_munic) }} - {{ trim($h->den_uni_feder) }} - {{ trim($h->pais_emp) }}</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td>TEL.: {{ trim($h->num_telefone) }} - FAX: {{ trim($h->num_fax) }}</td>
    </tr>
    <tr><td colspan="3" style="height: 5px;"></td></tr>
</table>

@elseif ($isArgentina)
{{-- ARGENTINA (UNIBOM emp 16) VARIANT: 3 columns --}}
<table cellspacing="0" cellpadding="3" style="table-layout: fixed;">
    <tr>
        <th colspan="3" align="right">{{ $datEmbarqueFormatted }}</th>
    </tr>
    <tr>
        <th width="38%" align="left">COMPRADOR / BUYER:</th>
        <th width="32%" align="left">CONSIGNATÁRIO / CONSIGNEE</th>
        <th width="30%" align="left">EXPORTEDOR / EXPORTER:</th>
    </tr>
    <tr>
        <td>{{ trim($h->texto1_buyer) }}</td>
        <td>{{ trim($h->texto1_consignat) }}</td>
        <td>{{ trim($h->den_razao_social) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto2_buyer) }}</td>
        <td>{{ trim($h->texto2_consignat) }}</td>
        <td>{{ trim($h->end_empresa) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto3_buyer) }}</td>
        <td>{{ trim($h->texto3_consignat) }}</td>
        <td>{{ trim($h->den_munic) }} - {{ trim($h->den_uni_feder) }} - {{ trim($h->pais_emp) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto4_buyer) }}</td>
        <td>{{ trim($h->texto4_consignat) }}</td>
        <td>CNPJ: {{ trim($h->num_cgc) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto5_buyer) }}</td>
        <td>{{ trim($h->texto5_consignat) }}</td>
        <td></td>
    </tr>
    <tr><td colspan="3" style="height: 5px;"></td></tr>
</table>

@else
{{-- DEFAULT VARIANT: 3 columns --}}
<table cellspacing="0" cellpadding="3" style="table-layout: fixed;">
    <tr>
        <th colspan="3" align="right">{{ $datEmbarqueFormatted }}</th>
    </tr>
    <tr>
        <th width="38%" align="left">COMPRADOR / BUYER:</th>
        <th width="32%" align="left">CONSIGNATÁRIO / CONSIGNEE</th>
        <th width="30%" align="left">EXPORTEDOR / EXPORTER:</th>
    </tr>
    <tr>
        <td>{{ trim($h->texto1_buyer) }}</td>
        <td>{{ trim($h->texto1_consignat) }}</td>
        <td>{{ trim($h->den_razao_social) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto2_buyer) }}</td>
        <td>{{ trim($h->texto2_consignat) }}</td>
        <td>{{ trim($h->end_empresa) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto3_buyer) }}</td>
        <td>{{ trim($h->texto3_consignat) }}</td>
        <td>{{ trim($h->den_munic) }}- {{ trim($h->den_uni_feder) }}- {{ trim($h->pais_emp) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto4_buyer) }}</td>
        <td>{{ trim($h->texto4_consignat) }}</td>
        <td>CNPJ: {{ trim($h->num_cgc) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto5_buyer) }}</td>
        <td>{{ trim($h->texto5_consignat) }}</td>
        <td></td>
    </tr>
    <tr><td colspan="3" style="height: 5px;"></td></tr>
</table>
@endif

{{-- ══════════════════════════════════════════════════════════════
     SECTION 4: SHIPPING DETAILS
     ══════════════════════════════════════════════════════════════ --}}
@if ($isArgentina)
<table cellspacing="0" cellpadding="3" style="table-layout: fixed;">
    <tr>
        <th width="33%" align="left">NÚMERO B/L:</th>
        <td colspan="2" align="left">{{ trim($h->num_bl) }}</td>
    </tr>
    <tr>
        <th align="left">DE:</th>
        <td colspan="2" align="left">{{ trim($h->den_munic) }}</td>
    </tr>
    <tr>
        <th align="left">PARA:</th>
        <td colspan="2" align="left">{{ trim($h->local_destino) }}</td>
    </tr>
    <tr>
        <th align="left">MARCACIÓN:</th>
        <td colspan="2">{{ trim($h->marca) }}</td>
    </tr>
    <tr>
        <th align="left">TRANSPORTISTA:</th>
        <td colspan="2" align="left">{{ trim($h->transportadora) }}</td>
    </tr>
    <tr>
        <th align="left">TERMO DEL PAGAMENTO:</th>
        <td colspan="2">{{ trim($h->cond_pgto_ingles) }}</td>
    </tr>
    <tr>
        <th align="left">TERMO DE LA ENTREGA:</th>
        <td colspan="2" align="left">{{ trim($h->local_destino) }}</td>
    </tr>
    <tr>
        <th align="left">EMBALAJE:</th>
        <td colspan="2" align="left">{{ trim($h->embal_invoice) }}</td>
    </tr>
    <tr>
        <th align="left">SELLO NR (SIF):</th>
        <td colspan="2">{{ trim($h->cod_lacre_sif) }}</td>
    </tr>
    <tr>
        <th align="left">PUERTO DE CARGA:</th>
        <td colspan="2" align="left">{{ trim($h->local_embarque) }}- {{ trim($h->pais_int) }}</td>
    </tr>
    <tr>
        <th align="left">DESTINO FINAL:</th>
        <td colspan="2">{{ $paisDestino }}</td>
    </tr>
    <tr>
        <th align="left">NCM:</th>
        <td colspan="2" align="left">{{ $ncm }}</td>
    </tr>
    <tr>
        <th align="left">EMBARCADO EN:</th>
        <td colspan="2" align="left">{{ $datEmbarqueFormatted }}</td>
    </tr>
    <tr>
        <th align="left">ORDEM DE COMPRA:</th>
        <td colspan="2">{{ trim($h->ordem) }}</td>
    </tr>
    <tr><td colspan="3" style="height: 5px;"></td></tr>
</table>

@else
{{-- USA + DEFAULT: English shipping details --}}
<table cellspacing="0" cellpadding="3" style="table-layout: fixed;">
    <tr>
        <th width="33%" align="left">B/L NUMBER:</th>
        <td colspan="2" align="left">{{ trim($h->num_bl) }}</td>
    </tr>
    <tr>
        <th align="left">DATED:</th>
        <td colspan="2" align="left">{{ $datEmbarqueFormatted }}</td>
    </tr>
    <tr>
        <th align="left">VESSEL NAME:</th>
        <td colspan="2" align="left">{{ trim($h->den_navio_aviao) }}</td>
    </tr>
    <tr>
        <th align="left">CONTAINER NUMBER:</th>
        <td colspan="2">{{ trim($h->cod_container) }}</td>
    </tr>
    <tr>
        <th align="left">SEAL NR (CNTR):</th>
        <td colspan="2" align="left">{{ trim($h->cod_lacre) }}</td>
    </tr>
    <tr>
        <th align="left">SEAL NR (SIF):</th>
        <td colspan="2">{{ trim($h->cod_lacre_sif) }}</td>
    </tr>
    <tr>
        <th align="left">PORT OF LOADING:</th>
        <td colspan="2">{{ trim($h->local_embarque) }}- {{ trim($h->pais_int) }}</td>
    </tr>
    <tr>
        <th align="left">FINAL PORT OF DISCHARGE:</th>
        <td colspan="2" align="left">{{ trim($h->local_destino) }}</td>
    </tr>
    <tr>
        <th align="left">FINAL DESTINATION:</th>
        <td colspan="2" align="left">{{ $paisDestino }}</td>
    </tr>
    <tr>
        <th align="left">MARKS:</th>
        <td colspan="2">{{ trim($h->marca) }}</td>
    </tr>
    <tr>
        <th align="left">PO:</th>
        <td colspan="2" align="left">{{ trim($h->ordem) }}</td>
    </tr>
    <tr>
        <th align="left">NCM:</th>
        <td colspan="2">{{ $ncm }}</td>
    </tr>
    <tr><td colspan="3" style="height: 5px;"></td></tr>
</table>
@endif

{{-- ══════════════════════════════════════════════════════════════
     SECTION 5: ITEMS TABLE
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
@if ($isArgentina)
    {{-- ARGENTINA ITEMS HEADER: 7 columns --}}
    <tr>
        <th align="center" width="85" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">CANTIDAD PALLETS DE MADERA</th>
        <th align="center" width="85" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">CANTIDAD DE CAJAS</th>
        <th align="center" width="85" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">PESO NETO (KG)</th>
        <th align="center" width="85" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">PESO BRUTO (KG)</th>
        <th align="center" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">DESCRICION DE LA MERCANCIA</th>
        <th align="center" width="85" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">PRECIO UNIT. {{ $denMoeda }}</th>
        <th align="center" width="85" class="b-t b-l b-r" style="background:{{ $bg }};color:{{ $fc }};">PRECIO TOTAL {{ $denMoeda }}</th>
    </tr>

    @foreach ($items as $item)
    <tr>
        <th align="center" class="b-t b-l">{{ $h->qtdo_palete ?? '' }}</th>
        <th align="center" class="b-t b-l">{{ number_format($item->tot_embalag ?? 0, 0, '.', ',') }}</th>
        <th align="center" class="b-t b-l">{{ number_format($item->qtd_pecas_solic ?? 0, 3, '.', ',') }}</th>
        <th align="center" class="b-t b-l">{{ number_format($item->rateio_palete ?? 0, 3, '.', ',') }}</th>
        <th align="center" class="b-t b-l">{{ trim($item->den_item_int ?? '') }}</th>
        <th align="center" class="b-t b-l">{{ number_format($item->val_tonelada ?? 0, 2, '.', ',') }}</th>
        <th align="center" class="b-t b-l b-r">{{ $denMoeda }} {{ number_format($item->val_tot_item ?? 0, 2, '.', ',') }}</th>
    </tr>
    @endforeach

@else
    {{-- USA + DEFAULT ITEMS HEADER: 6 columns --}}
    <tr>
        <th width="7%" align="center" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">{{ trim($h->tip_embal) }}</th>
        <th width="11%" align="center" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">NET WEIGHT (KG)</th>
        <th width="11%" align="center" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">GROSS WEIGHT (KG)</th>
        <th width="46%" align="left" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">DESCRIPTION OF PRODUCT</th>
        <th width="10%" align="center" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">UNIT. PRICE {{ $denMoeda }}</th>
        <th width="15%" align="center" class="b-t b-l b-r" style="background:{{ $bg }};color:{{ $fc }};">AMOUNT {{ $denMoeda }}</th>
    </tr>

    @foreach ($items as $item)
    <tr>
        <th align="center" class="b-t b-l">{{ number_format($item->tot_embalag ?? 0, 0, '.', ',') }}</th>
        <th align="center" class="b-t b-l">{{ number_format($item->qtd_pecas_solic ?? 0, 3, '.', ',') }}</th>
        <th align="center" class="b-t b-l">{{ number_format($item->rateio_palete ?? 0, 3, '.', ',') }}</th>
        <th align="left" class="b-t b-l">{{ trim($item->den_item_int ?? '') }}</th>
        <th align="center" class="b-t b-l">{{ number_format($item->val_tonelada ?? 0, 2, '.', ',') }}</th>
        <th align="center" class="b-t b-l b-r">{{ $denMoeda }} {{ number_format($item->val_tot_item ?? 0, 2, '.', ',') }}</th>
    </tr>
    @endforeach
@endif

    {{-- FOOTER ROWS --}}
    {{-- Linha FREIGHT (val_frete_embarque do header) --}}
    <tr>
        <th colspan="4" align="left" class="b-t b-l">FREIGHT</th>
        <th colspan="2" align="center" class="b-t b-l b-r">{{ $denMoeda }} {{ $fmtValFrete }}</th>
    </tr>
    {{-- Condição de pagamento --}}
    <tr>
        <th colspan="4" align="left" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">{{ trim($h->cond_pgto_ingles) }}</th>
        <th colspan="2" align="center" class="b-t b-l b-r"></th>
    </tr>
    {{-- TOTAL --}}
    <tr>
        <th colspan="2" align="left" class="b-b b-l">TOTAL</th>
        <th align="center" class="b-b">{{ trim($h->cod_incoterm) }}</th>
        <td align="center" class="b-b">{{ trim($h->local_destino) }}</td>
        <th colspan="2" align="center" class="b-b b-l b-r">{{ $denMoeda }} {{ $fmtTotValItem }}</th>
    </tr>
</table>

{{-- texto_docs section (condicional) --}}
@if (trim($h->texto_docs1 ?? '') || trim($h->texto_docs2 ?? '') || trim($h->texto_docs3 ?? ''))
<table cellspacing="0" cellpadding="3">
    @if (trim($h->texto_docs1 ?? ''))
    <tr><th align="left" class="b-b b-l b-r">{{ trim($h->texto_docs1) }}</th></tr>
    @endif
    @if (trim($h->texto_docs2 ?? ''))
    <tr><th align="left" class="b-b b-l b-r">{{ trim($h->texto_docs2) }}</th></tr>
    @endif
    @if (trim($h->texto_docs3 ?? ''))
    <tr><th align="left" class="b-b b-l b-r">{{ trim($h->texto_docs3) }}</th></tr>
    @endif
    <tr><th class="b-b b-l b-r" style="background:{{ $bg }};">&nbsp;</th></tr>
</table>
@endif

<table cellspacing="0" cellpadding="0">
    <tr><td style="height: 5px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 6: TOTALS SUMMARY
     ══════════════════════════════════════════════════════════════ --}}
@if ($isCAD)
{{-- CAD: no bank details, just totals + signature --}}
<table cellspacing="0" cellpadding="3">
    @if ($isArgentina)
    <tr>
        <td width="23%" align="left" class="b-t b-b b-l">TOTAL PALETES DE MADERA:</td>
        <th width="13%" align="center" class="b-all">{{ $h->qtdo_palete ?? '' }}</th>
        <td width="64%"></td>
    </tr>
    <tr>
        <td align="left" class="b-b b-l">TOTAL PESO NETO (KG):</td>
        <th align="center" class="b-b b-l b-r">{{ number_format($totEmbalag, 0, '.', ',') }}</th>
        <td></td>
    </tr>
    <tr>
        <td align="left" class="b-b b-l">TOTAL PESO NETO (KG):</td>
        <th align="center" class="b-b b-l b-r">{{ number_format($totPesoLiq, 3, '.', ',') }}</th>
        <td></td>
    </tr>
    <tr>
        <td align="left" class="b-b b-l">TOTAL PESO BRUTO (KG):</td>
        <th align="center" class="b-b b-l b-r">{{ number_format($pesoBrutoHeader, 3, '.', ',') }}</th>
        <td></td>
    </tr>
    @else
    <tr>
        <td width="23%" align="left" class="b-t b-b b-l">TOTAL {{ trim($h->tip_embal) }}:</td>
        <th width="13%" align="right" class="b-all">{{ number_format($totEmbalag, 0, '.', ',') }}</th>
        <td width="64%"></td>
    </tr>
    <tr>
        <td align="left" class="b-b b-l">TOTAL NET WEIGHT (KG):</td>
        <th align="right" class="b-b b-l b-r">{{ number_format($totPesoLiq, 3, '.', ',') }}</th>
        <td></td>
    </tr>
    <tr>
        <td align="left" class="b-b b-l">TOTAL GROSS WEIGHT (KG):</td>
        <th align="right" class="b-b b-l b-r">{{ number_format($pesoBrutoHeader, 3, '.', ',') }}</th>
        <td></td>
    </tr>
    @endif
</table>

<table cellspacing="0" cellpadding="0">
    <tr><td style="height: 5px;"></td></tr>
</table>

@else
{{-- NOT CAD: totals + bank details --}}
<table cellspacing="0" cellpadding="3">
    @if ($isArgentina)
    <tr>
        <td width="23%" align="left" class="b-t b-b b-l">TOTAL PALETES DE MADERA:</td>
        <th width="13%" align="center" class="b-all">{{ $h->qtdo_palete ?? '' }}</th>
        <td width="64%"></td>
    </tr>
    <tr>
        <td align="left" class="b-b b-l">TOTAL PESO NETO (KG):</td>
        <th align="center" class="b-b b-l b-r">{{ number_format($totEmbalag, 0, '.', ',') }}</th>
        <td></td>
    </tr>
    <tr>
        <td align="left" class="b-b b-l">TOTAL PESO NETO (KG):</td>
        <th align="center" class="b-b b-l b-r">{{ number_format($totPesoLiq, 3, '.', ',') }}</th>
        <td></td>
    </tr>
    <tr>
        <td align="left" class="b-b b-l">TOTAL PESO BRUTO (KG):</td>
        <th align="center" class="b-b b-l b-r">{{ number_format($pesoBrutoHeader, 3, '.', ',') }}</th>
        <td></td>
    </tr>
    @else
    <tr>
        <td width="23%" align="left" class="b-t b-b b-l">TOTAL {{ trim($h->tip_embal) }}:</td>
        <th width="13%" align="right" class="b-all">{{ number_format(round($totEmbalag), 0, '.', ',') }}</th>
        <td width="64%"></td>
    </tr>
    <tr>
        <td align="left" class="b-b b-l">TOTAL NET WEIGHT (KG):</td>
        <th align="right" class="b-b b-l b-r">{{ number_format($totPesoLiq, 3, '.', ',') }}</th>
        <td></td>
    </tr>
    <tr>
        <td align="left" class="b-b b-l">TOTAL GROSS WEIGHT (KG):</td>
        <th align="right" class="b-b b-l b-r">{{ number_format($pesoBrutoHeader, 3, '.', ',') }}</th>
        <td></td>
    </tr>
    @endif
</table>

<table cellspacing="0" cellpadding="0">
    <tr><td style="height: 5px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 7: BANK DETAILS (always full, no cod_banco distinction)
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <th width="36%" align="left" class="b-t b-l">BENEFICIARY NAME AND ADDRESS:</th>
        <th align="left" class="b-t b-l b-r">{{ trim($h->den_razao_social) }}</th>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l b-r"><b>ADDRESS:</b> {{ trim($h->end_empresa) }} {{ trim($h->den_munic) }} {{ trim($h->den_uni_feder) }} {{ trim($h->pais_emp) }} {{ trim($h->cod_cep) }}</td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l b-r"><b>PHONE:</b> {{ trim($h->num_telefone) }}</td>
    </tr>
    <tr>
        <th align="left" class="b-t b-l">BENEFICIARY BANK AND ACCOUNT NUMBER / </th>
        <th align="left" class="b-t b-l b-r">{{ trim($h->bank) }}</th>
    </tr>
    <tr>
        <th align="left" class="b-l">IBAN NUMBER:</th>
        <td class="b-l b-r"><b>ACCOUNT:</b> {{ trim($h->account_number) }}</td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l b-r"><b>IBAN:</b> {{ trim($h->iban ?? '') }}</td>
    </tr>
    <tr>
        <th align="left" class="b-t b-l">BENEFICIARY BANK SWIFT CODE:</th>
        <td class="b-t b-l b-r"><b>FIELD 57A ACCOUNT WITH:</b> {{ trim($h->account_57) }}</td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l b-r"><b>SWIFT CODE:</b> {{ trim($h->swift_code_57) }}</td>
    </tr>
    <tr>
        <th align="left" class="b-t b-l">SORT CODE OR ABA NUMBER:</th>
        <td class="b-t b-l b-r"><b>ABA NUMBER:</b> {{ trim($h->branch_number) }}</td>
    </tr>
    <tr>
        <th align="left" class="b-t b-l">CORRESPONDENT / INTERMEDIARY BANK </th>
        <th align="left" class="b-t b-l b-r">FIELD 56A INTERMEDIARY BANK:</th>
    </tr>
    <tr>
        <th align="left" class="b-l">DETAILS WITH SWIFT CODE (FIELD 56A):</th>
        <td class="b-l b-r">{{ trim($h->den_razao_social) }}</td>
    </tr>
    <tr>
        <td class="b-b b-l"></td>
        <td class="b-b b-l b-r"><b>SWIFT CODE:</b> {{ trim($h->swift_code_56) }}</td>
    </tr>
</table>
@endif

{{-- ══════════════════════════════════════════════════════════════
     SECTION 8: SIGNATURE
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="2">
    <tr>
        <td width="50%" align="center"></td>
        <td width="50%" align="center">
            @if(file_exists($ass))
                <img src="{{ $ass }}" alt="Assinatura" width="150">
            @endif
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 9: FOOTER (Company name + Department)
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <td colspan="3" class="b-b"></td>
    </tr>
    <tr>
        <td width="25%"></td>
        <td width="50%" align="center"><b>{{ trim($h->den_razao_social) }}</b></td>
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

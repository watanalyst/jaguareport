@php
    use Carbon\Carbon;

    // --- Branding ---
    $bg   = $branding['bg_color'];
    $fc   = $branding['font_color'];
    $logo = public_path($branding['logo']);
    $sif  = public_path($branding['sif'] ?? '');
    $ass  = public_path($branding['signature']);

    $h = $header;

    // --- Variant Detection ---
    $paisDestino = strtoupper(trim($h->pais_destino ?? ''));
    $idioma      = strtoupper(trim($h->idioma ?? ''));
    $spanishCountries = ['ARGENTINA', 'CHILE', 'URUGUAY', 'COLOMBIA'];

    $isUSA     = ($paisDestino === 'UNITED STATES OF AMERICA');
    $isSpanish = ($idioma === 'ESPANHOL' && in_array($paisDestino, $spanishCountries));

    // --- Title ---
    $docTitle = $isSpanish ? 'FACTURA' : 'INVOICE';
    $deptName = $isSpanish ? 'DEPARTAMENTO DE EXPORTACIÓN' : 'EXPORT DEPARTMENT';

    // --- Date Formatting ---
    $datEmbarque = $h->dat_embarque ? Carbon::parse($h->dat_embarque) : null;
    $datAtualRaw = $h->dat_atual ?? $h->dat_embarque ?? null;
    $datAtual    = $datAtualRaw ? Carbon::parse($datAtualRaw) : now();

    if ($isSpanish && $datEmbarque) {
        $mesesES = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',
                    6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',
                    10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
        $datEmbarqueFormatted = $datEmbarque->day . ' de ' . $mesesES[$datEmbarque->month] . ' de ' . $datEmbarque->year;
    } elseif ($datEmbarque) {
        $datEmbarqueFormatted = $datEmbarque->locale('en')->isoFormat('MMMM D, YYYY');
    } else {
        $datEmbarqueFormatted = '';
    }

    if ($isSpanish) {
        $mesesES = $mesesES ?? [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',
                    6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',
                    10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
        $datAtualFormatted = $datAtual->day . ' de ' . $mesesES[$datAtual->month] . ' de ' . $datAtual->year;
    } else {
        $datAtualFormatted = $datAtual->locale('en')->isoFormat('MMMM D, YYYY');
    }

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
    $totValFrete  = 0;
    $totQtdoPalete = 0;

    foreach ($items as $item) {
        $totEmbalag    += $item->tot_embalag ?? 0;
        $totPesoLiq    += $item->qtd_pecas_solic ?? 0;
        $totPesoBruto  += $item->rateio_palete ?? 0;
        $totValItem    += $item->val_tot_item ?? 0;
        $totValFrete   += $item->val_frete_embarque ?? 0;
        $totQtdoPalete += $item->qtdo_palete ?? 0;
    }

    $totPesoBruto = round($totPesoBruto);

    $pctAdiant  = $h->pct_adiant ?? 0;
    $valAdiant  = $h->val_receb_adto ?? 0;
    $valRestPagar = $pctAdiant > 0 ? ($totValItem - $valAdiant) : 0;
    $denMoeda   = trim($h->den_moeda_abrev ?? '');

    $isCAD      = strtoupper(trim($h->obs_cnd_pgto ?? '')) === 'CAD';
    $isKuwait   = ($paisDestino === 'KUWAIT');
    $hasBankFull = (trim($h->cod_banco ?? '') != '5');

    $vgmFormatted = number_format($h->vgm ?? 0, 2, ',', '.');

    // Spanish FOB calculation
    if ($isSpanish) {
        $valorFob = $totValItem - $totValFrete;
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ trim($h->proforma) }}-{{ trim($h->embarque) }}</title>
    <style>
        @page { margin: 30px 25px 20px 25px; }

        * { font-family: Helvetica, Arial, sans-serif; font-size: 10.5px; line-height: 1.3; }

        body { margin: 0; padding: 0; color: #333; }

        p { margin: 0; padding: 0; }

        table { border-collapse: separate; border-spacing: 0; width: 100%; }

        th, td { vertical-align: top; padding: 2px 4px; font-size: 10.5px; }

        .inv-title { font-size: 16px; font-weight: bold; }

        .th-header {
            font-weight: bold;
            text-align: center;
            padding: 2px 4px;
            color: {{ $fc }};
            background-color: {{ $bg }};
        }

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
@if ($isSpanish)
{{-- SPANISH VARIANT: 2 columns + separate exporter --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <th colspan="2" align="right">{{ $datAtualFormatted }}</th>
    </tr>
    <tr>
        <th width="45%" align="left">COMPRADOR / BUYER:</th>
        <th width="55%" align="left">CONSIGNATÁRIO / CONSIGNEE</th>
    </tr>
    <tr>
        <td>{{ trim($h->texto1_buyer) }}</td>
        <td>{{ trim($h->texto1_consignat) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto2_buyer) }}</td>
        <td>{{ trim($h->texto2_consignat) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto3_buyer) }}</td>
        <td>{{ trim($h->texto3_consignat) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto4_buyer) }}</td>
        <td>{{ trim($h->texto4_consignat) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto5_buyer) }}</td>
        <td>{{ trim($h->texto5_consignat) }}</td>
    </tr>
    <tr><td colspan="2" style="height: 5px;"></td></tr>
    <tr>
        <th align="left">EXPORTADOR / EXPORTER:</th>
    </tr>
    <tr>
        <td>{{ trim($h->den_razao_social) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->end_empresa) }} - {{ trim($h->den_munic) }} - {{ trim($h->den_uni_feder) }} - {{ trim($h->pais_emp) }}</td>
    </tr>
    <tr>
        <td>CNPJ: {{ trim($h->num_cgc) }}</td>
    </tr>
    <tr><td colspan="2" style="height: 5px;"></td></tr>
</table>

@elseif ($isUSA)
{{-- USA VARIANT: 3 columns with full exporter details --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <th colspan="3" align="right">{{ $datAtualFormatted }}</th>
    </tr>
    <tr>
        <th width="27%" align="left">COMPRADOR / BUYER:</th>
        <th width="27%" align="left">CONSIGNATÁRIO / CONSIGNEE</th>
        <th width="46%" align="left">EXPORTADOR / EXPORTER:</th>
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
    <tr>
        <td></td>
        <td></td>
        <td>WEBSITE: {{ trim($h->site) }}</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td>E-MAIL: {{ trim($h->email_contato) }}</td>
    </tr>
    <tr><td colspan="3" style="height: 5px;"></td></tr>
</table>

@else
{{-- DEFAULT VARIANT: 3 columns --}}
@php
    $is108911 = (trim($h->cod_consignat ?? '') == '108911');
    $wBuyer = $is108911 ? '27%' : '38%';
    $wConsig = $is108911 ? '27%' : '32%';
    $wExport = $is108911 ? '46%' : '30%';
@endphp
<table cellspacing="0" cellpadding="3">
    <tr>
        <th colspan="3" align="right">{{ $datAtualFormatted }}</th>
    </tr>
    <tr>
        <th width="{{ $wBuyer }}" align="left">COMPRADOR / BUYER:</th>
        <th width="{{ $wConsig }}" align="left">CONSIGNATÁRIO / CONSIGNEE</th>
        <th width="{{ $wExport }}" align="left">EXPORTEDOR / EXPORTER:</th>
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
        @if ($is108911)
            <td></td>
        @else
            <td>WEBSITE: {{ trim($h->site) }}</td>
        @endif
    </tr>
    @if (! $is108911)
    <tr>
        <td></td>
        <td></td>
        <td>E-MAIL: {{ trim($h->email_contato) }}</td>
    </tr>
    @endif
    <tr><td colspan="3" style="height: 5px;"></td></tr>
</table>
@endif

{{-- ══════════════════════════════════════════════════════════════
     SECTION 4: SHIPPING DETAILS
     ══════════════════════════════════════════════════════════════ --}}
@if ($isSpanish)
<table cellspacing="0" cellpadding="3">
    <tr>
        <th width="200" align="left">DE:</th>
        <td>{{ trim($h->den_munic) }}</td>
    </tr>
    <tr>
        <th align="left">PARA:</th>
        <td>{{ trim($h->local_destino) }}</td>
    </tr>
    <tr>
        <th align="left">MARCACIÓN:</th>
        <td>{{ trim($h->marca) }}</td>
    </tr>
    <tr>
        <th align="left">TRANSPORTISTA:</th>
        <td>{{ trim($h->transportadora) }}</td>
    </tr>
    <tr>
        <th align="left">TERMO DEL PAGAMENTO:</th>
        <td>{{ trim($h->cond_pgto_ingles) }}</td>
    </tr>
    <tr>
        <th align="left">TERMO DE LA ENTREGA:</th>
        <td>{{ trim($h->local_destino) }}</td>
    </tr>
    <tr>
        <th align="left">EMBALAJE:</th>
        <td>{{ trim($h->embal_invoice) }}</td>
    </tr>
    <tr>
        <th align="left">EMBARCADO EN:</th>
        <td>{{ $datEmbarqueFormatted }}</td>
    </tr>
    <tr>
        <th align="left">ORDEN DE COMPRA:</th>
        <td>{{ trim($h->ordem) }}</td>
    </tr>
    <tr><td colspan="2" style="height: 5px;"></td></tr>
</table>

@else
{{-- USA + DEFAULT: English shipping details --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <th width="200" align="left">B/L NUMBER:</th>
        <td>{{ trim($h->num_bl) }}</td>
    </tr>
    <tr>
        <th align="left">DATED:</th>
        <td>{{ $datEmbarqueFormatted }}</td>
    </tr>
    <tr>
        <th align="left">VESSEL NAME:</th>
        <td>{{ trim($h->den_navio_aviao) }}</td>
    </tr>
    <tr>
        <th align="left">CONTAINER NUMBER:</th>
        <td>{{ trim($h->cod_container) }}</td>
    </tr>
    <tr>
        <th align="left">SEAL NR (CNTR):</th>
        <td>{{ trim($h->cod_lacre) }}</td>
    </tr>
    <tr>
        <th align="left">SEAL NR (SIF):</th>
        <td>{{ trim($h->cod_lacre_sif) }}</td>
    </tr>
    <tr>
        <th align="left">PORT OF LOADING:</th>
        <td>{{ trim($h->local_embarque) }} - {{ trim($h->pais_int) }}</td>
    </tr>
    <tr>
        <th align="left">FINAL PORT OF DISCHARGE:</th>
        <td>{{ trim($h->local_destino) }}</td>
    </tr>
    <tr>
        <th align="left">FINAL DESTINATION:</th>
        <td>{{ $paisDestino }}</td>
    </tr>
    <tr>
        <th align="left">MARKS:</th>
        <td>{{ trim($h->marca) }}</td>
    </tr>
    <tr>
        <th align="left">NCM:</th>
        <td>{{ $ncm }}</td>
    </tr>
    <tr>
        <th align="left">PO:</th>
        <td>{{ trim($h->ordem) }}</td>
    </tr>
    @if (trim($h->ies_termografo ?? ''))
    <tr>
        <th align="left">TERMOGRAFO:</th>
        <td>{{ trim($h->ies_termografo) }}</td>
    </tr>
    @endif
    @if ($isKuwait)
    <tr>
        <th align="left">HS CODE:</th>
        <td>020714</td>
    </tr>
    @endif
    <tr><td colspan="2" style="height: 5px;"></td></tr>
</table>
@endif

{{-- ══════════════════════════════════════════════════════════════
     SECTION 5: ITEMS TABLE
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
@if ($isSpanish)
    {{-- SPANISH ITEMS HEADER: 7 columns --}}
    <tr>
        <th align="center" width="85" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">CANTIDAD PALLETS DE MADERA</th>
        <th align="center" width="85" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">CANTIDAD DE CAJAS</th>
        <th align="center" width="85" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">PESO NETO (KG)</th>
        <th align="center" width="85" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">PESO BRUTO (KG)</th>
        <th align="center" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">DESCRIPCIÓN DE LA MERCANCIA</th>
        <th align="center" width="85" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">PRECIO UNIT. {{ $denMoeda }}</th>
        <th align="center" width="85" class="b-t b-l b-r" style="background:{{ $bg }};color:{{ $fc }};">PRECIO TOTAL {{ $denMoeda }}</th>
    </tr>

    @foreach ($items as $item)
    <tr>
        <th align="center" class="b-t b-l">{{ $item->qtdo_palete ?? '' }}</th>
        <th align="center" class="b-t b-l">{{ number_format($item->tot_embalag ?? 0, 0, '.', ',') }}</th>
        <th align="center" class="b-t b-l">{{ number_format($item->qtd_pecas_solic ?? 0, 3, '.', ',') }}</th>
        <th align="center" class="b-t b-l">{{ number_format($item->rateio_palete ?? 0, 3, ',', ',') }}</th>
        <th align="left" class="b-t b-l">{{ trim($item->den_item ?? '') }} <br>{{ trim($item->den_item_int ?? '') }}</th>
        <th align="center" class="b-t b-l">{{ number_format($item->val_tonelada ?? 0, 2, '.', ',') }}</th>
        <th align="center" class="b-t b-l b-r">{{ $denMoeda }} {{ number_format($item->val_tot_item ?? 0, 2, '.', ',') }}</th>
    </tr>
    @endforeach

    {{-- Spanish footer rows --}}
    <tr>
        <th colspan="7" style="height: 15px; background:{{ $bg }};color:{{ $fc }};" class="b-t b-b b-l b-r"></th>
    </tr>
    <tr>
        <th colspan="5" align="right" class="b-b b-l">VALOR FOB:</th>
        <th colspan="2" align="center" class="b-b b-l b-r">{{ $denMoeda }} {{ number_format($valorFob ?? 0, 2, '.', ',') }}</th>
    </tr>
    <tr>
        <th colspan="5" align="right" class="b-b b-l">MONTO DE FLETE EXTERNO:</th>
        <th colspan="2" align="center" class="b-b b-l b-r">{{ $denMoeda }} {{ number_format($totValFrete, 2, '.', ',') }}</th>
    </tr>
    <tr>
        <th colspan="2" align="left" class="b-b b-l">PRECIO TOTAL A PAGAR</th>
        <th align="center" class="b-b">{{ trim($h->cod_incoterm) }}</th>
        <th colspan="2" align="left" class="b-b">{{ trim($h->local_destino) }}</th>
        <th colspan="2" align="center" class="b-b b-l b-r">{{ $denMoeda }} {{ number_format($totValItem, 2, '.', ',') }}</th>
    </tr>

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
    @php
        $pesoLiq   = $item->qtd_pecas_solic ?? 0;
        $pesoBruto = round($item->rateio_palete ?? 0);
    @endphp
    <tr>
        <th align="center" class="b-t b-l">{{ number_format($item->tot_embalag ?? 0, 0, '.', ',') }}</th>
        <th align="center" class="b-t b-l">{{ number_format($pesoLiq, 3, '.', ',') }}</th>
        <th align="center" class="b-t b-l">{{ number_format($pesoBruto, 3, '.', ',') }}</th>
        <th align="left" class="b-t b-l">{{ trim($item->den_item_int ?? '') }}</th>
        <th align="center" class="b-t b-l">{{ number_format($item->val_tonelada ?? 0, 2, '.', ',') }}</th>
        <th align="center" class="b-t b-l b-r">{{ $denMoeda }} {{ number_format($item->val_tot_item ?? 0, 2, '.', ',') }}</th>
    </tr>
    @endforeach

    {{-- USA + DEFAULT footer rows --}}
    @if ($pctAdiant > 0)
        <tr>
            <th colspan="4" align="left" class="b-t b-b b-l">ADVANCED</th>
            <th colspan="2" align="center" class="b-t b-b b-l b-r">{{ $denMoeda }} {{ number_format($valAdiant, 2, '.', ',') }}</th>
        </tr>
        <tr>
            <th colspan="4" align="left" class="b-b b-l" style="background:{{ $bg }};color:{{ $fc }};">{{ trim($h->cond_pgto_ingles) }}</th>
            <th colspan="2" align="center" class="b-b b-l b-r">{{ $denMoeda }} {{ number_format($valRestPagar, 2, '.', ',') }}</th>
        </tr>
        <tr>
            <th colspan="2" align="left" class="b-b b-l">Total</th>
            <th align="center" class="b-b">{{ trim($h->cod_incoterm) }}</th>
            <td align="center" class="b-b">{{ trim($h->local_destino) }}</td>
            <th colspan="2" align="center" class="b-b b-l b-r">{{ $denMoeda }} {{ number_format($totValItem, 2, '.', ',') }}</th>
        </tr>
    @elseif (trim($h->texto_docs1 ?? ''))
        <tr>
            <th colspan="4" align="left" class="b-t b-b b-l" style="background:{{ $bg }};color:{{ $fc }};">{{ trim($h->cond_pgto_ingles) }}</th>
            <th colspan="2" align="center" class="b-t b-b b-l b-r"></th>
        </tr>
        <tr>
            <th colspan="2" align="left" class="b-b b-l">Total</th>
            <th align="center" class="b-b">{{ trim($h->cod_incoterm) }}</th>
            <td align="center" class="b-b">{{ trim($h->local_destino) }}</td>
            <th colspan="2" align="center" class="b-b b-l b-r">{{ $denMoeda }} {{ number_format($totValItem, 2, '.', ',') }}</th>
        </tr>
    @else
        @if (! $isUSA && ($h->val_invoice ?? 0) > 0)
        {{-- Default variant with VAL_INVOICE --}}
        <tr>
            <th colspan="3" align="left" class="b-t b-b b-l"></th>
            <th align="center" class="b-t b-b b-l">{{ trim($h->operacao) }}</th>
            <th colspan="2" align="center" class="b-t b-b b-l b-r">{{ $denMoeda }} {{ number_format($h->val_invoice, 2, '.', ',') }}</th>
        </tr>
        <tr>
            <th colspan="4" align="left" class="b-b b-l" style="background:{{ $bg }};color:{{ $fc }};">{{ trim($h->cond_pgto_ingles) }}</th>
            <th colspan="2" align="center" class="b-b b-l b-r">{{ $denMoeda }} {{ number_format($totValItem - ($h->val_invoice ?? 0), 2, '.', ',') }}</th>
        </tr>
        @else
        <tr>
            <th colspan="4" align="left" class="b-t b-b b-l">ADVANCED</th>
            <th colspan="2" align="center" class="b-t b-b b-l b-r">{{ $denMoeda }} {{ number_format($valAdiant, 2, '.', ',') }}</th>
        </tr>
        <tr>
            <th colspan="4" align="left" class="b-t b-b b-l" style="background:{{ $bg }};color:{{ $fc }};">{{ trim($h->cond_pgto_ingles) }}</th>
            <th colspan="2" align="center" class="b-b b-l b-r">{{ $denMoeda }} {{ number_format($valRestPagar, 2, '.', ',') }}</th>
        </tr>
        @endif
        <tr>
            <th colspan="2" align="left" class="b-b b-l">Total</th>
            <th align="center" class="b-b">{{ trim($h->cod_incoterm) }}</th>
            <td align="center" class="b-b">{{ trim($h->local_destino) }}</td>
            <th colspan="2" align="center" class="b-b b-l b-r">{{ $denMoeda }} {{ number_format($totValItem, 2, '.', ',') }}</th>
        </tr>
    @endif
@endif
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 5b: TEXTO_DOCS (conditional, after items table)
     ══════════════════════════════════════════════════════════════ --}}
@if (trim($h->texto_docs1 ?? '') || trim($h->texto_docs2 ?? '') || trim($h->texto_docs3 ?? ''))
<table cellspacing="0" cellpadding="3">
    @if (trim($h->texto_docs1 ?? ''))
    <tr>
        <th align="left" class="b-b b-l b-r">{{ trim($h->texto_docs1) }}</th>
    </tr>
    @endif
    @if (trim($h->texto_docs2 ?? ''))
    <tr>
        <th align="left" class="b-b b-l b-r">{{ trim($h->texto_docs2) }}</th>
    </tr>
    @endif
    @if (trim($h->texto_docs3 ?? ''))
    <tr>
        <th align="left" class="b-b b-l b-r">{{ trim($h->texto_docs3) }}</th>
    </tr>
    @endif
    <tr>
        <th class="b-b b-l b-r" style="background:{{ $bg }};">&nbsp;</th>
    </tr>
</table>
@endif

<table cellspacing="0" cellpadding="0">
    <tr><td style="height: 5px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 6: TOTALS SUMMARY
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
@if ($isSpanish)
    <tr>
        <td width="23%" align="left" class="b-t b-b b-l">TOTAL PALETES DE MADERA:</td>
        <th width="13%" align="right" class="b-t b-b b-l b-r">{{ $totQtdoPalete }}</th>
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
@else
    <tr>
        <td width="23%" align="left" class="b-t b-b b-l">TOTAL {{ trim($h->tip_embal) }}:</td>
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
@endif
</table>

<table cellspacing="0" cellpadding="0">
    <tr><td style="height: 5px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 7: BANK DETAILS (only if NOT CAD)
     ══════════════════════════════════════════════════════════════ --}}
@if (! $isCAD)
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
    @if ($hasBankFull)
    <tr>
        <td class="b-l"></td>
        <td class="b-l b-r"><b>IBAN:</b> {{ trim($h->iban) }}</td>
    </tr>
    @endif
    <tr>
        <th align="left" class="b-t b-l">BENEFICIARY BANK SWIFT CODE:</th>
        <td class="b-t b-l b-r"><b>FIELD 57A ACCOUNT WITH:</b> {{ trim($h->account_57) }}</td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l b-r"><b>SWIFT CODE:</b> {{ trim($h->swift_code_57) }}</td>
    </tr>
    <tr>
        <th align="left" class="{{ $hasBankFull ? 'b-t b-l' : 'b-t b-b b-l' }}">SORT CODE OR ABA NUMBER:</th>
        <td class="{{ $hasBankFull ? 'b-t b-l b-r' : 'b-t b-b b-l b-r' }}"><b>ABA NUMBER:</b> {{ trim($h->branch_number) }}</td>
    </tr>
    @if ($hasBankFull)
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
    @endif
</table>

<table cellspacing="0" cellpadding="0">
    <tr><td style="height: 05px;"></td></tr>
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
    <tr>
        <td style="height: 05px;"></td>
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

{{-- TODO: Kuwait packing list table (deferred - complex cross-DB queries to MIMSJAGUA/MIMSIPU) --}}

</body>
</html>

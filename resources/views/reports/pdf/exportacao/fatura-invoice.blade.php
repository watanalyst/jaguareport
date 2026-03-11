@php
    use Carbon\Carbon;

    // --- Branding ---
    $bg   = $branding['bg_color'];
    $fc   = $branding['font_color'];
    $logo = public_path($branding['logo']);
    $sif  = public_path($branding['sif'] ?? '');
    $ass  = public_path($branding['signature']);

    $h = $header;

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

    $pesoBrutoHeader = $h->rateio_palete ?? $totPesoBruto;
    $denMoeda        = trim($h->den_moeda_abrev ?? '');
    $isCAD           = strtoupper(trim($h->obs_cnd_pgto ?? '')) === 'CAD';

    // Format helpers
    $fmtTotValItem = number_format($totValItem, 2, '.', ',');
@endphp
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Fatura Invoice {{ trim($h->proforma) }}-{{ trim($h->embarque) }}</title>
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
        <th align="center" class="inv-title">FATURA / INVOICE</th>
    </tr>
    <tr>
        <th align="center">{{ trim($h->proforma) }}-{{ trim($h->embarque) }}</th>
    </tr>
    <tr>
        <td class="b-b"></td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTIONS 3+4: IMPORTADOR/EXPORTADOR + SHIPPING DETAILS
     (tabela única para alinhamento perfeito no DomPDF)
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3" style="table-layout: fixed;">
    <tr>
        <th colspan="2" align="right">{{ $datEmbarqueFormatted }}</th>
    </tr>
    <tr>
        <th width="50%" align="left">IMPORTADOR / BUYER:</th>
        <th width="50%" align="left">EXPORTADOR / EXPORTED BY:</th>
    </tr>
    <tr>
        <td>{{ trim($h->texto1_consignat) }}</td>
        <td>{{ trim($h->den_razao_social) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto2_consignat) }}</td>
        <td>{{ trim($h->end_empresa) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto3_consignat) }}</td>
        <td>{{ trim($h->den_munic) }}- {{ trim($h->den_uni_feder) }}- {{ trim($h->pais_emp) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto4_consignat) }}</td>
        <td>CNPJ: {{ trim($h->num_cgc) }}</td>
    </tr>
    <tr>
        <td>{{ trim($h->texto5_consignat) }}</td>
        <td></td>
    </tr>
    <tr><td colspan="2" style="height: 8px;"></td></tr>
    {{-- Shipping Details (bilingual) --}}
    <tr>
        <th align="left">NÚMERO DO B/L - B/L NUMBER:</th>
        <td align="left">{{ trim($h->num_bl) }}</td>
    </tr>
    <tr>
        <th align="left">DATA / DATED:</th>
        <td align="left">{{ $datEmbarqueFormatted }}</td>
    </tr>
    <tr>
        <th align="left">NÚMERO DO NAVIO / VESSEL NAME:</th>
        <td align="left">{{ trim($h->den_navio_aviao) }}</td>
    </tr>
    <tr>
        <th align="left">NÚMERO DO CONTAINER / CONTAINER NUMBER:</th>
        <td>{{ trim($h->cod_container) }}</td>
    </tr>
    <tr>
        <th align="left">NÚMERO DO LACRE (CNTR) / SEAL NR (CNTR):</th>
        <td align="left">{{ trim($h->cod_lacre) }}</td>
    </tr>
    <tr>
        <th align="left">NÚMERO DO LACRE (SIF) / SEAL NR (SIF):</th>
        <td>{{ trim($h->cod_lacre_sif) }}</td>
    </tr>
    <tr>
        <th align="left">PORTO DE EMBARQUE / PORT OF LOADING:</th>
        <td>{{ trim($h->local_embarque) }}- {{ trim($h->pais_int) }}</td>
    </tr>
    <tr>
        <th align="left">PORTO DE DESCARGA / FINAL PORT OF DISCHARGE:</th>
        <td align="left">{{ trim($h->local_destino) }}</td>
    </tr>
    <tr>
        <th align="left">DESTINO FINAL / FINAL DESTINATION:</th>
        <td align="left">{{ trim($h->local_destino) }}</td>
    </tr>
    <tr>
        <th align="left">PO:</th>
        <td align="left">{{ trim($h->ordem) }}</td>
    </tr>
    <tr>
        <th align="left">NCM:</th>
        <td>{{ $ncm }}</td>
    </tr>
    <tr><td colspan="2" style="height: 5px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 5: ITEMS TABLE
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <th width="7%" align="center" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">CAIXAS / {{ trim($h->tip_embal) }}</th>
        <th width="11%" align="center" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">PESO LÍQUIDO / NET WEIGHT (KG)</th>
        <th width="11%" align="center" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">PESO BRUTO / GROSS WEIGHT (KG)</th>
        <th width="46%" align="left" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">DESCRIÇÃO DO PRODUTO / DESCRIPTION OF PRODUCT</th>
        <th width="10%" align="center" class="b-t b-l" style="background:{{ $bg }};color:{{ $fc }};">PREÇO UNITÁRIO / UNIT. PRICE {{ $denMoeda }}</th>
        <th width="15%" align="center" class="b-t b-l b-r" style="background:{{ $bg }};color:{{ $fc }};">VALOR TOTAL / AMOUNT {{ $denMoeda }}</th>
    </tr>

    @foreach ($items as $item)
    <tr>
        <th align="center" class="b-t b-l">{{ number_format($item->tot_embalag ?? 0, 0, '.', ',') }}</th>
        <th align="center" class="b-t b-l">{{ number_format($item->qtd_pecas_solic ?? 0, 3, '.', ',') }}</th>
        <th align="center" class="b-t b-l">{{ number_format($item->rateio_palete ?? 0, 3, '.', ',') }}</th>
        <th align="left" class="b-t b-l">
            @if(trim($item->den_item ?? '')){{ trim($item->den_item) }} / <br>@endif
            {{ trim($item->den_item_int ?? '') }}
        </th>
        <th align="center" class="b-t b-l">{{ number_format($item->val_tonelada ?? 0, 2, '.', ',') }}</th>
        <th align="center" class="b-t b-l b-r">{{ $denMoeda }} {{ number_format($item->val_tot_item ?? 0, 2, '.', ',') }}</th>
    </tr>
    @endforeach

    {{-- FOOTER ROWS --}}
    {{-- Condição de pagamento --}}
    <tr>
        <th colspan="4" align="left" class="b-t b-b b-l" style="background:{{ $bg }};color:{{ $fc }};">{{ trim($h->cond_pgto_ingles) }}</th>
        <td colspan="2" align="center" class="b-t b-b b-l b-r"></td>
    </tr>
    {{-- TOTAL --}}
    <tr>
        <th colspan="1" align="left" class="b-b b-l">TOTAL</th>
        <th colspan="2" align="center" class="b-b">CUSTO E FRETE / {{ trim($h->cod_incoterm) }}</th>
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
</table>
@endif

<table cellspacing="0" cellpadding="0">
    <tr><td style="height: 5px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 6: TOTALS SUMMARY
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
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
</table>

<table cellspacing="0" cellpadding="0">
    <tr><td style="height: 5px;"></td></tr>
</table>

@if (! $isCAD)
{{-- ══════════════════════════════════════════════════════════════
     SECTION 7: BANK DETAILS
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
        <td align="center">EXPORT DEPARTMENT</td>
        <td></td>
    </tr>
</table>

</body>
</html>

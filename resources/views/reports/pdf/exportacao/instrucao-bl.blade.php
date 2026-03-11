@php
    use Carbon\Carbon;

    $bg    = $branding['bg_color'];
    $fc    = $branding['font_color'];
    $logo  = public_path($branding['logo']);

    $h = $header;

    $datAtualiz  = $h->dat_atualiz  ? Carbon::parse($h->dat_atualiz)->locale('en')->isoFormat('MMMM D, YYYY')  : '';
    $datEmbarque = $h->dat_embarque ? Carbon::parse($h->dat_embarque)->locale('en')->isoFormat('MMMM D, YYYY') : '';
    $prevEmbarque = $h->prev_embarque ? Carbon::parse($h->prev_embarque)->locale('en')->isoFormat('MMMM/YYYY') : '';

    $vgmFormatted      = number_format($h->vgm ?? 0, 2, '.', ',');
    $totComissaoFmt    = number_format($totComissao, 2, '.', ',');
    $taraContFmt       = number_format($h->tara_cont ?? 0, 0, '.', ',');
    $denMoeda          = trim($h->den_moeda_abrev ?? '');

    // Cubagem: pad to 8 rows
    $cubagemRows = $cubagem->toArray();
    $totCubagem  = 0;
    for ($i = 0; $i < 8; $i++) {
        if (! isset($cubagemRows[$i])) {
            $cubagemRows[$i] = (object) ['cod_item' => '', 'ncm' => '', 'cubagem' => null];
        }
        $totCubagem += $cubagemRows[$i]->cubagem ?? 0;
    }

    $textoCharges = [
        'DESPESAS NO PORTO DE <u>ORIGEM</u>(TFC): <b>PREPAID</b>',
        'DESPESAS NO PORTO DE <u>DESTINO</u>(TFC): <b>COLLECT</b>',
        'DESPESAS DE TRANSPORTE NA <u>ORIGEM</u>(TFC): <b>PREPAID</b>',
        'DESPESAS DE TRANSPORTE NO <u>DESTINO</u>(TFC): <b>COLLECT</b>',
        'FRETE MAR&Iacute;TIMO: <b>' . trim($h->frt_incoterms) . '</b>',
        'FRETE PAGO EM: <b>ORIGIN</b>',
        '',
        '',
    ];

    // Items totals
    $totalVolumeItens = 0;
    $totPesoLiq = 0;
    $totPesoBrt = 0;
    $totValItem = 0;
    foreach ($items as $item) {
        $totalVolumeItens += $item->tot_embalag ?? 0;
        $totPesoLiq       += $item->qtd_pecas_solic ?? 0;
        $totPesoBrt       += $item->rateio_palete ?? 0;
        $totValItem       += $item->val_tot_item ?? 0;
    }

    $adiantamento = ($totValItem * (($h->pct_adiant ?? 0) / 100));
    $valFrete     = $h->val_frete_embarque ?? 0;
    $valFob       = $totValItem - $valFrete;

    $isAgente108708 = (trim($h->cod_agente_carga ?? '') === '108708');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Instrução BL {{ $h->proforma }}-{{ trim($h->embarque) }}</title>
    <style>
        @page { margin: 30px 25px 20px 25px; }

        * { font-family: Helvetica, Arial, sans-serif; font-size: 10px; line-height: 1.3; }

        body { margin: 0; padding: 0; color: #333; }
        p { margin: 0; padding: 0; }

        table { border-collapse: collapse; width: 100%; }
        th, td { vertical-align: top; padding: 2px 4px; }

        .th-header {
            font-weight: bold;
            text-align: center;
            padding: 2px 4px;
            color: {{ $fc }};
            background-color: {{ $bg }};
        }

        .b-all   { border: 1px solid #000; }
        .b-t     { border-top: 1px solid #000; }
        .b-b     { border-bottom: 1px solid #000; }
        .b-l     { border-left: 1px solid #000; }
        .b-r     { border-right: 1px solid #000; }
    </style>
</head>
<body>

{{-- ══════════════════════════════════════════════════════════════
     LOGO HEADER
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="2">
    <tr>
        <td width="20%" rowspan="4" align="center">
            @if(file_exists($logo))
                <img src="{{ $logo }}" alt="LOGO" width="100">
            @endif
        </td>
        <td width="60%" align="center" style="font-size: 14px; font-weight: bold;">{{ trim($h->marca) }}</td>
        <td width="20%" rowspan="4" align="center">
            @if(file_exists($logo))
                <img src="{{ $logo }}" alt="LOGO" width="100">
            @endif
        </td>
    </tr>
    <tr><td align="center">{{ trim($h->den_razao_social) }}</td></tr>
    <tr><td align="center">SITE: {{ trim($h->site) }}</td></tr>
    <tr><td align="center">PHONE: {{ trim($h->num_telefone) }}</td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     MAIN TABLE (8 columns)
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">

    {{-- TITLE BAR --}}
    <tr>
        <th colspan="8" class="th-header b-all" align="center" style="font-size: 14px;">Instruções para o BL e DUE.</th>
    </tr>

    {{-- DESPACHANTE / DATAS --}}
    <tr>
        <td colspan="1" class="b-t b-l" align="left">DESPACHANTE:</td>
        <th colspan="3" class="b-t" align="left">TRIASUL</th>
        <th colspan="1" class="b-t" align="left">DATA INSTRUÇÃO:</th>
        <th colspan="3" class="b-t b-r" align="left">{{ $datAtualiz }}</th>
    </tr>
    <tr>
        <td colspan="1" class="b-l" align="left">PROFORMA / INVOICE:</td>
        <th colspan="3" align="left">{{ trim($h->proforma) }}-{{ trim($h->embarque) }}</th>
        <th colspan="1" align="left">DATA SAÍDA DO NAVIO:</th>
        <th colspan="3" class="b-r" align="left">{{ $datEmbarque }}</th>
    </tr>
    <tr>
        <td colspan="1" class="b-l" align="left">ORDEM</td>
        <td colspan="3" align="left">{{ trim($h->ordem) }}</td>
        <th colspan="1" align="left">NÚMERO DO BL:</th>
        <th colspan="3" class="b-r" align="left">{{ trim($h->num_bl) }}</th>
    </tr>

    {{-- 1) SHIPPER --}}
    <tr>
        <th colspan="8" class="th-header b-all" style="text-align: left;">1) SHIPPER:</th>
    </tr>
    <tr>
        <td colspan="8" class="b-l b-r" align="left">{{ trim($h->den_razao_social) }}</td>
    </tr>
    <tr>
        <td colspan="8" class="b-l b-r" align="left">{{ trim($h->end_empresa) }} - {{ trim($h->den_munic) }} - {{ trim($h->den_uni_feder) }} - {{ trim($h->pais_emp) }}</td>
    </tr>
    <tr>
        <td colspan="8" class="b-l b-r" align="left">CNPJ: {{ trim($h->num_cgc) }}</td>
    </tr>
    <tr>
        <th colspan="8" class="b-b b-l b-r" align="left">{{ trim($h->em_nome_de) }}</th>
    </tr>


    {{-- 2) CONSIGNEE --}}
    <tr>
        <th colspan="8" class="th-header b-all" style="text-align: left;">2) CONSIGNEE:</th>
    </tr>
    <tr>
        <th colspan="8" class="b-l b-r" align="left">{{ trim($h->texto1_consignat) }}</th>
    </tr>
    <tr>
        <th colspan="8" class="b-l b-r" align="left">{{ trim($h->texto2_consignat) }}</th>
    </tr>
    <tr>
        <th colspan="8" class="b-l b-r" align="left">{{ trim($h->texto3_consignat) }}</th>
    </tr>
    <tr>
        <th colspan="8" class="b-l b-r" align="left">{{ trim($h->texto4_consignat) }}</th>
    </tr>
    <tr>
        <th colspan="8" class="b-b b-l b-r" align="left">{{ trim($h->texto5_consignat) }}</th>
    </tr>

    {{-- 3) NOTIFY + 4) SECOND NOTIFY --}}
    <tr>
        <th colspan="5" class="th-header b-all" style="text-align: left;">3) NOTIFY:</th>
        <th colspan="3" class="th-header b-all" style="text-align: left;">4) SECOND NOTIFY:</th>
    </tr>
    <tr>
        <th colspan="5" class="b-l b-r" align="left">{{ trim($h->texto1_notify) }}</th>
        <th colspan="3" class="b-l b-r" align="left">{{ trim($h->texto1_notify2) }}</th>
    </tr>
    <tr>
        <th colspan="5" class="b-l b-r" align="left">{{ trim($h->texto2_notify) }}</th>
        <th colspan="3" class="b-l b-r" align="left">{{ trim($h->texto2_notify2) }}</th>
    </tr>
    <tr>
        <th colspan="5" class="b-l b-r" align="left">{{ trim($h->texto3_notify) }}</th>
        <th colspan="3" class="b-l b-r" align="left">{{ trim($h->texto3_notify2) }}</th>
    </tr>
    <tr>
        <th colspan="5" class="b-l b-r" align="left">{{ trim($h->texto4_notify) }}</th>
        <th colspan="3" class="b-l b-r" align="left">{{ trim($h->texto4_notify2) }}</th>
    </tr>
    <tr>
        <th colspan="5" class="b-b b-l b-r" align="left">{{ trim($h->texto5_notify) }}</th>
        <th colspan="3" class="b-b b-l b-r" align="left">{{ trim($h->texto5_notify2) }}</th>
    </tr>

    {{-- 5) FAZER CONSTAR NO BL --}}
    <tr>
        <th colspan="8" class="th-header b-all" style="text-align: left;">5) FAZER CONSTAR NO BL:</th>
    </tr>

    @if (! $isAgente108708)
    {{-- Layout 5+3 --}}
    <tr>
        <td colspan="5" class="b-t b-l">* CLEAN SHIPPED ON BOARD</td>
        <th colspan="3" class="b-t b-l b-r" align="left">BL COMMENTS (INTTRA):</th>
    </tr>
    <tr>
        <td colspan="5" class="b-l">* SHIPPED ON BOARD</td>
        <td colspan="3" class="b-l b-r" align="left">{{ trim($h->texto_inst_bl1) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="b-l">* GOODS SHIPPED UNDER <b>-{{ trim($h->cont_temperatura) }}&deg;C</b></td>
        <td colspan="3" class="b-l b-r" align="left">{{ trim($h->texto_inst_bl2) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="b-l">* FREIGHT {{ trim($h->frt_incoterms) }}</td>
        <td colspan="3" class="b-l b-r" align="left">{{ trim($h->texto_inst_bl3) }}</td>
    </tr>
    <tr>
        <th colspan="5" class="b-l" align="left">* FREIGHT AS PER AGREEMENT</th>
        <td colspan="3" class="b-l b-r" align="left">{{ trim($h->texto_inst_bl4) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="b-l" align="left">* SHIPPING MARKS: <b>{{ trim($h->marca) }}</b></td>
        <td colspan="3" class="b-b b-l b-r" align="left">{{ trim($h->texto_inst_bl5) }}</td>
    </tr>
    @if ($h->ies_termografo)
    <tr>
        <th colspan="5" class="b-l" align="left">* {{ trim($h->madeira) }}</th>
        <td colspan="3" class="b-b b-l b-r" align="left">THERMOGRAPH: {{ trim($h->ies_termografo) }}</td>
    </tr>
    @else
    <tr>
        <th colspan="8" class="b-l b-r" align="left">* {{ trim($h->madeira) }}</th>
    </tr>
    @endif
    @else
    {{-- Layout 4+4 (agente 108708) --}}
    <tr>
        <td colspan="4" class="b-t b-l">* CLEAN SHIPPED ON BOARD</td>
        <th colspan="4" class="b-t b-l b-r" align="left">BL COMMENTS (INTTRA):</th>
    </tr>
    <tr>
        <td colspan="4" class="b-l">* SHIPPED ON BOARD</td>
        <td colspan="4" class="b-l b-r" align="left">{{ trim($h->texto_inst_bl1) }}</td>
    </tr>
    <tr>
        <td colspan="4" class="b-l">* GOODS SHIPPED UNDER <b>-{{ trim($h->cont_temperatura) }}&deg;C</b></td>
        <td colspan="4" class="b-l b-r" align="left">{{ trim($h->texto_inst_bl2) }}</td>
    </tr>
    <tr>
        <td colspan="4" class="b-l">* FREIGHT {{ trim($h->frt_incoterms) }}</td>
        <td colspan="4" class="b-l b-r" align="left">{{ trim($h->texto_inst_bl3) }}</td>
    </tr>
    <tr>
        <th colspan="4" class="b-l" align="left">* FREIGHT AS PER AGREEMENT</th>
        <td colspan="4" class="b-l b-r" align="left">{{ trim($h->texto_inst_bl4) }}</td>
    </tr>
    <tr>
        <td colspan="4" class="b-l" align="left">* SHIPPING MARKS: <b>{{ trim($h->marca) }}</b></td>
        <td colspan="4" class="b-l b-r" align="left">{{ trim($h->texto_inst_bl5) }}</td>
    </tr>
    @if ($h->ies_termografo)
    <tr>
        <th colspan="4" class="b-l b-r" align="left">* {{ trim($h->madeira) }}</th>
        <td colspan="4" class="b-l b-r" align="left">THERMOGRAPH: {{ trim($h->ies_termografo) }}</td>
    </tr>
    @else
    <tr>
        <th colspan="4" class="b-l b-r" align="left">* {{ trim($h->madeira) }}</th>
        <td colspan="4" class="b-l b-r" align="left"></td>
    </tr>
    @endif
    <tr>
        <th colspan="4" class="b-l" align="left">* VGM {{ $vgmFormatted }}</th>
        <td colspan="4" class="b-b b-l b-r" align="left"></td>
    </tr>
    @endif

    @if (! $isAgente108708)
    <tr>
        <th colspan="8" class="b-l b-r" align="left">* VGM {{ $vgmFormatted }}</th>
    </tr>
    @endif
    <tr>
        <td class="b-b b-l" style="height: 3px;"></td>
        <td colspan="7" class="b-b b-r"></td>
    </tr>
    <tr>
        <td colspan="5" class="b-l" align="center">* CBM</td>
        <th colspan="3" class="b-r" align="left"><u>INDIVIDUAL CHARGES(INTTRA):</u></th>
    </tr>

    {{-- CBM ITEMS (8 rows) --}}
    @for ($i = 0; $i < 8; $i++)
    <tr>
        <td class="b-t b-l">ITEM</td>
        <td class="b-t b-l">{{ $i + 1 }}</td>
        <td class="b-t b-l">{{ trim($cubagemRows[$i]->cod_item ?? '') }}</td>
        <td class="b-t b-l">{{ trim($cubagemRows[$i]->ncm ?? '') }}</td>
        <td class="b-t b-l b-r" align="right">{{ $cubagemRows[$i]->cubagem }}</td>
        <td colspan="3" class="b-r" align="left">{!! $textoCharges[$i] !!}</td>
    </tr>
    @endfor

    {{-- TOTAL CBM --}}
    <tr>
        <td colspan="4" align="center" class="b-t b-b b-l">TOTAL CBM:</td>
        <td class="b-t b-b" align="right">{{ $totCubagem }}</td>
        <td colspan="3" class="b-b b-l b-r"></td>
    </tr>

</table>

{{-- ══════════════════════════════════════════════════════════════
     TABLE 2 - SHIPPING DETAILS
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <td width="20%" class="b-l" align="left">NOTA FISCAL:</td>
        <th width="80%" class="b-r" align="left">{{ trim($h->nota_fiscal) }}</th>
    </tr>
    <tr>
        <td class="b-l" align="left">VESSEL'S NAME:</td>
        <th class="b-r" align="left">{{ trim($h->den_navio_aviao) }}</th>
    </tr>
    <tr>
        <td class="b-l" align="left">PORT OF LOADING:</td>
        <th class="b-r" align="left">{{ trim($h->local_embarque) }}</th>
    </tr>
    <tr>
        <td class="b-l" align="left">PORT PLACE OF DELIVERY:</td>
        <th class="b-r" align="left">{{ trim($h->local_destino) }}</th>
    </tr>
    <tr>
        <td class="b-l" align="left">PORT OF TRANSHIPMENT:</td>
        <td class="b-r" align="left">{{ trim($h->porto_transbordo) }}</td>
    </tr>
    <tr>
        <td class="b-l" align="left">FINAL DESTINATION:</td>
        <th class="b-r" align="left">{{ trim($h->pais_destino) }}</th>
    </tr>
    <tr>
        <td class="b-l" align="left">BOOKING Nº:</td>
        <th class="b-r" align="left">{{ trim($h->cod_booking) }}</th>
    </tr>
    <tr>
        <td class="b-l" align="left">CONTAINER NUMBER:</td>
        <th class="b-r" align="left">{{ trim($h->cod_container) }}</th>
    </tr>
    <tr>
        <td class="b-l" align="left">SEAL NUMBER(CNTR):</td>
        <th class="b-r" align="left">{{ trim($h->cod_lacre) }}</th>
    </tr>
    <tr>
        <td class="b-l" align="left">SEAL NUMBER(SIF):</td>
        <th class="b-r" align="left">{{ trim($h->cod_lacre_sif) }}</th>
    </tr>
    <tr>
        <td class="b-l" align="left">INSPECTION CERTIFICATE:</td>
        <th class="b-r" align="left">{{ trim($h->ies_csi_dsc) }}</th>
    </tr>
    <tr>
        <td class="b-l" align="left">REFER. SIG SIF:</td>
        <th class="b-r" align="left">{{ trim($h->refer_sig_sif) }}</th>
    </tr>
    <tr>
        <td class="b-l" align="left">TARE:</td>
        <th class="b-r" align="left">{{ $taraContFmt }}</th>
    </tr>
    <tr>
        <td class="b-l" align="left">MARCA DO PRODUTO:</td>
        <th class="b-r" align="left">{{ trim($h->marca) }}</th>
    </tr>
    <tr>
        <td class="b-l" align="left">AGENTE:</td>
        <th class="b-r" align="left">{{ trim($h->nom_agente) }}</th>
    </tr>
    <tr>
        <td class="b-b b-l" align="left">ARMADOR:</td>
        <th class="b-b b-r" align="left">{{ trim($h->armador_reduz) }}</th>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     TABLE 3 - CARTONS + PESO/VALUE + FREIGHT/CFR/FOB
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">

    {{-- 6) CARTONS TABLE HEADER --}}
    <tr>
        <th colspan="3" align="center" rowspan="2" class="b-t b-b b-l" style="background:{{ $bg }};color:{{ $fc }};border-color:#000;">CARTONS</th>
        <th align="center" rowspan="2" class="b-t b-b b-l" style="background:{{ $bg }};color:{{ $fc }};border-color:#000;">NCM</th>
        <th colspan="2" align="center" rowspan="2" class="b-t b-b b-l" style="background:{{ $bg }};color:{{ $fc }};border-color:#000;">PRODUTOS</th>
        <th colspan="2" align="center" class="b-t b-l b-r" style="background:{{ $bg }};color:{{ $fc }};border-color:#000;">PESO / CAIXA</th>
    </tr>
    <tr>
        <th class="b-t b-b b-l b-r" align="center" style="background:{{ $bg }};color:{{ $fc }};border-color:#000;">LIQUIDO</th>
        <th class="b-t b-b b-r" align="center" style="background:{{ $bg }};color:{{ $fc }};border-color:#000;">BRUTO</th>
    </tr>

    {{-- CARTONS ITEMS --}}
    @php $seq = 0; @endphp
    @foreach ($items as $item)
    @php
        $seq++;
        $pesoLqFmt = is_numeric($item->qtd_padr_embal) ? number_format($item->qtd_padr_embal, 2, '.', ',') : '0.00';
        $pesoBrFmt = is_numeric($item->qtd_bruto)      ? number_format($item->qtd_bruto, 2, '.', ',')      : '0.00';
        $volFmt    = is_numeric($item->tot_embalag)     ? number_format($item->tot_embalag, 0, '.', ',')    : '0';
    @endphp
    <tr>
        <td align="left" class="b-l">ITEM</td>
        <td align="right">{{ $seq }}</td>
        <th align="right">{{ $volFmt }}</th>
        <th align="center" rowspan="2" class="b-b b-l">{{ trim($item->ncm) }}</th>
        <th colspan="2" align="left" rowspan="2" class="b-b b-l">{{ trim($item->den_item_int) }}</th>
        <td align="center" rowspan="2" class="b-b b-l">{{ $pesoLqFmt }}</td>
        <td align="center" rowspan="2" class="b-b b-r">{{ $pesoBrFmt }}</td>
    </tr>
    <tr>
        <th colspan="3" align="left" class="b-b b-l">{{ trim($item->cod_item) }}</th>
    </tr>
    @endforeach

    {{-- CARTONS TOTAL --}}
    <tr>
        <th colspan="2" align="center" class="b-b b-l b-r" style="background:{{ $bg }};color:{{ $fc }};border-color:{{ $bg }};">TOTAL</th>
        <th align="right" class="b-all" style="background:{{ $bg }};color:{{ $fc }};border-color:{{ $bg }};">{{ number_format($totalVolumeItens, 2, '.', ',') }}</th>
        <th colspan="5" align="right" class="b-all" style="background:{{ $bg }};color:{{ $fc }};border-color:{{ $bg }};"></th>
    </tr>
    <tr><td colspan="8" class="b-t b-b b-l b-r" style="height: 3px;"></td></tr>

    {{-- PESO / VALUE HEADER --}}
    <tr>
        <td colspan="3" class="th-header" style="border-color:{{ $bg }};"></td>
        <th align="center" class="th-header" style="border-color:{{ $bg }};">PESO LIQUIDO</th>
        <th align="center" class="th-header" style="border-color:{{ $bg }};">PESO BRUTO</th>
        <th align="center" class="th-header" style="border-color:{{ $bg }};">{{ $denMoeda }}/TON</th>
        <th align="center" colspan="2" class="th-header" style="border-color:{{ $bg }};">TOTAL</th>
    </tr>

    {{-- PESO / VALUE ITEMS --}}
    @php $seq2 = 0; @endphp
    @foreach ($items as $item)
    @php
        $seq2++;
        $pesoLiq = number_format($item->qtd_pecas_solic, 2, ',', '.');
        $pesoBrt = number_format($item->rateio_palete, 2, ',', '.');
        $valTon  = number_format($item->val_tonelada, 2, '.', ',');
        $valItem = number_format($item->val_tot_item, 2, '.', ',');
    @endphp
    <tr>
        <td width="7%" align="left" class="b-t b-l">ITEM:</td>
        <td width="5%" align="left" class="b-t">{{ $seq2 }}</td>
        <td width="12%" align="left" class="b-t"></td>
        <td width="14%" align="right" class="b-t">{{ $pesoLiq }}</td>
        <td width="18%" align="right" class="b-t">{{ $pesoBrt }}</td>
        <td width="14%" align="right" class="b-t">{{ $valTon }}</td>
        <td colspan="2" width="30%" align="right" class="b-t b-r">{{ $denMoeda }} {{ $valItem }}</td>
    </tr>
    @endforeach

    {{-- TOTAL BAR --}}
    <tr>
        <td colspan="3" class="th-header" style="border-color:{{ $bg }};" align="right">TOTAL</td>
        <th class="th-header" style="border-color:{{ $bg }};" align="right">{{ number_format($totPesoLiq, 2, ',', '.') }}</th>
        <th class="th-header" style="border-color:{{ $bg }};" align="right">{{ number_format($totPesoBrt, 2, ',', '.') }}</th>
        <th class="th-header" style="border-color:{{ $bg }};" align="right">TOTAL</th>
        <th colspan="2" class="th-header" style="border-color:{{ $bg }};" align="right">{{ $denMoeda }} {{ number_format($totValItem, 2, '.', ',') }}</th>
    </tr>

    {{-- FREIGHT --}}
    <tr>
        <td colspan="6" align="right" class="b-t b-b b-l">FREIGHT</td>
        <th colspan="2" align="right" class="b-t b-b b-r">{{ $denMoeda }} {{ number_format($valFrete, 2, '.', ',') }}</th>
    </tr>

    {{-- ADIANTAMENTO --}}
    <tr>
        <td colspan="6" align="right" class="b-b b-l">(<b>NÃO</b> INFORMAR NO RE) PAGAMENTO ANTECIPADO</td>
        <th colspan="2" align="right" class="b-b b-r">{{ $denMoeda }} {{ number_format($adiantamento, 2, '.', ',') }}</th>
    </tr>

    {{-- CFR --}}
    <tr>
        <th colspan="6" class="th-header" style="border-color:{{ $bg }};" align="right">CFR</th>
        <th colspan="2" class="th-header" style="border-color:{{ $bg }};" align="right">{{ $denMoeda }} {{ number_format($totValItem, 2, '.', ',') }}</th>
    </tr>

    {{-- FOB --}}
    <tr>
        <th colspan="6" align="right" class="b-t b-b b-l">FOB</th>
        <th colspan="2" align="right" class="b-t b-b b-r">{{ $denMoeda }} {{ number_format($valFob, 2, '.', ',') }}</th>
    </tr>

    {{-- TOTAL CFR --}}
    <tr>
        <th colspan="5" class="th-header" style="border-color:{{ $bg }};" align="right">TOTAL</th>
        <th class="th-header" style="border-color:{{ $bg }};" align="right">CFR</th>
        <th colspan="2" class="th-header" style="border-color:{{ $bg }};" align="right">{{ $denMoeda }} {{ number_format($totValItem, 2, '.', ',') }}</th>
    </tr>


</table>

{{-- ══════════════════════════════════════════════════════════════
     TABLE 4 - OBS + COMISSÃO + FORMA PAGAMENTO + OBSERVAÇÃO
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">

    {{-- 6) OBS --}}
    @if (trim($h->forma_comissao) === 'REMETER')
    <tr>
        <td colspan="2" class="b-t b-l" align="left">OBS:</td>
        <td colspan="3" class="b-t b-l" align="left">INFORMAR ATO CONCESSORIO NO DUE.</td>
        <th colspan="1" class="b-t" align="left">Num.AC: {{ trim($h->num_ac) }}</th>
        <th colspan="2" class="b-r" align="left">{{ $itemAC }}</th>
    </tr>
    <tr>
        <td colspan="2" class="b-b b-l"></td>
        <td colspan="3" class="b-b b-l" align="left">NAO INFORMAR A COMISSAO NO DUE.</td>
        <th colspan="1" class="b-b" align="left">{{ trim($h->forma_comissao) }}</th>
        <th colspan="2" class="b-b b-r"></th>
    </tr>
    @else
    <tr>
        <td colspan="2" class="b-t b-l" align="left">OBS:</td>
        <td colspan="3" class="b-t b-l" align="left">INFORMAR ATO CONCESSORIO NO DUE.</td>
        <th colspan="1" class="b-t" align="left">Num.AC: {{ trim($h->num_ac) }}</th>
        <th colspan="2" class="b-t b-r" align="left">{{ $itemAC }}</th>
    </tr>
    <tr>
        <td colspan="2" class="b-b b-l"></td>
        <td colspan="3" class="b-b b-l" align="left">INFORMAR A COMISSAO NO DUE.</td>
        <th colspan="1" class="b-b" align="left">{{ trim($h->forma_comissao) }}</th>
        <th colspan="2" class="b-b b-r"></th>
    </tr>
    @endif

    {{-- 7) COMISSÃO --}}
    <tr>
        <th colspan="8" class="th-header b-all" style="text-align: left;">7) COMISSÃO:</th>
    </tr>
    <tr>
        <td class="b-t b-l" colspan="4" align="center">VALOR FOB: ({{ $denMoeda }})</td>
        <td class="b-t" align="center">COMISSÃO</td>
        <td class="b-t" align="left">{{ number_format($h->pct_agente ?? 0, 2, ',', '.') }}%</td>
        <td class="b-t b-r" colspan="2" align="left">TRADER:</td>
    </tr>
    <tr>
        <th class="b-b b-l" colspan="4" align="center">${{ number_format($valFob, 2, '.', ',') }}</th>
        <th class="b-b" align="center">{{ $totComissaoFmt }}</th>
        <td class="b-b"></td>
        <td class="b-b b-r" colspan="2"></td>
    </tr>

    {{-- 8) FORMA DE PAGAMENTO --}}
    <tr>
        <th colspan="8" class="th-header b-all" style="text-align: left;">8) FORMA DE PAGAMENTO:</th>
    </tr>
    <tr>
        <td colspan="4" class="b-t b-b b-l" align="center">{{ trim($h->cond_pgto_ingles) }}</td>
        <td class="b-t b-b" align="center"></td>
        <td class="b-t b-b" align="left">Preço FOB</td>
        <td colspan="2" class="b-t b-b b-r"></td>
    </tr>

    {{-- 9) OBSERVAÇÃO --}}
    <tr>
        <th colspan="8" class="th-header b-all" style="text-align: left;">9) OBSERVAÇÃO:</th>
    </tr>
    <tr>
        <td colspan="4" class="b-t b-b b-l" align="center">{{ trim($h->campo_obs1) }}</td>
        <td class="b-t b-b" align="center"></td>
        <td class="b-t b-b" align="left"></td>
        <td colspan="2" class="b-t b-b b-r"></td>
    </tr>

    {{-- SAUDI ARABIA conditional --}}
    @if (trim($h->pais_destino) === 'SAUDI ARABIA')
    <tr>
        <td colspan="8" align="left">THIS CONTAINER IS EQUIPED WITH A PROBE TEMPERATURE CONNECTED WITH THE CARGO TO RECORD</td>
    </tr>
    <tr>
        <td colspan="8" align="left">THE TEMPERATURE FROM THE BEGINNING TO THE END OF THE TRIP</td>
    </tr>
    <tr>
        <td colspan="8" align="left">THE TEMPERATURE ON CONTAINER IS SET AT MINUS 18 CELSIUS DEGREES</td>
    </tr>
    @endif

</table>

</body>
</html>

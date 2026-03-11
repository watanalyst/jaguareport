@php
    use Carbon\Carbon;

    $bg    = $branding['bg_color'];
    $fc    = $branding['font_color'];
    $logo  = public_path($branding['logo']);
    $sif   = public_path($branding['sif']);
    $ass   = public_path($branding['signature']);

    $h = $header;

    $datInclusao  = Carbon::parse($h->dat_inclusao)->locale('en')->isoFormat('MMMM D, YYYY');
    $prevEmbarque = $h->prev_embarque ? Carbon::parse($h->prev_embarque)->locale('en')->isoFormat('MMMM/YYYY') : '';

    $totVal = 0;
    $totQtd = 0;
    foreach ($items as $item) {
        $totVal += $item->val_tot_item;
        $totQtd += $item->qtd_pecas_solic;
    }
    $adiantamento = ($totVal * ($h->pct_adiant ?? 0)) / 100;
    $denMoeda = $items->first()->den_moeda_abrev ?? '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Proforma {{ $h->proforma }}</title>
    <style>
        @page { margin: 40px 35px 30px 35px; }

        * { font-family: Helvetica, Arial, sans-serif; font-size: 10px; }

        body { margin: 0; padding: 0; color: #333; }

        p { margin: 0; padding: 0; }

        .prof-title {
                font-size: 22px;
                font-weight: bold;
                letter-spacing: 2px;
                margin-top: 5px;
                display: inline-block;
        }

        table { border-collapse: collapse; width: 100%; }

        th, td { vertical-align: top; }

        .th-header {
            font-weight: bold;
            text-align: center;
            padding: 3px 4px;
            color: {{ $fc }};
            background-color: {{ $bg }};
        }

        .justified { text-align: justify; }

        /* Border utility classes */
        .b-all   { border: 1px solid #000; }
        .b-t     { border-top: 1px solid #000; }
        .b-b     { border-bottom: 1px solid #000; }
        .b-l     { border-left: 1px solid #000; }
        .b-r     { border-right: 1px solid #000; }

        .clause p { margin: 2px 0; line-height: 1.35; }
    </style>
</head>
<body>

{{-- ══════════════════════════════════════════════════════════════
     LOGO + PROFORMA NUMBER
     ══════════════════════════════════════════════════════════════ --}}
<table width="100%" cellspacing="0" cellpadding="2">
    <tr>
        <td align="center">
            @if(file_exists($logo))
                <img src="{{ $logo }}" alt="LOGO" width="170"><br>
            @endif
            <span class="prof-title">PROFORMA &nbsp;&nbsp;&nbsp;{{ $h->proforma }}</span>
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     TABLE 1: DATE + SHIPPER + IMPORTER + INCOTERMS + CONTAINER + LEGALIZATION
     All sections share 6 columns for consistent alignment.
     Column widths defined by LEGALIZATION row: 20% | 20% | 8% | 12% | 18% | 22%
     ══════════════════════════════════════════════════════════════ --}}
<table width="100%" cellspacing="0" cellpadding="2">

    {{-- CITY + DATE --}}
    <tr>
        <td colspan="6" align="right">{{ trim($h->den_munic) }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $datInclusao }}</td>
    </tr>

    {{-- SHIPPER / PRODUCER / EXPORTER --}}
    <tr>
        <th colspan="6" class="th-header b-all">SHIPPER / PRODUCER / EXPORTER</th>
    </tr>
    <tr>
        <td colspan="4" class="b-l">{{ trim($h->den_empresa) }}</td>
        <td>COUNTRY OF ORIGIN:</td>
        <td class="b-r">{{ trim($h->pais_int) }}</td>
    </tr>
    <tr>
        <td colspan="4" class="b-l">CNPJ: {{ trim($h->num_cgc) }}</td>
        <td>PRODUCING STATE:</td>
        <td class="b-r">{{ trim($h->den_uni_feder) }}</td>
    </tr>
    <tr>
        <td colspan="4" class="b-l">{{ trim($h->end_empresa) }}</td>
        <td>PRODUCING PLANT:</td>
        <td class="b-r">{{ trim($h->sif) }}</td>
    </tr>
    <tr>
        <td colspan="4" class="b-l">{{ trim($h->den_munic) }} - {{ trim($h->den_uni_feder) }} - {{ trim($h->pais_emp) }}</td>
        <td>BRAND:</td>
        <td class="b-r">{{ trim($h->marca) }}</td>
    </tr>
    <tr>
        <td colspan="4" class="b-l">TEL: +55 {{ trim($h->num_telefone) }} - FAX: +55 {{ trim($h->num_fax) }}</td>
        <td>SHIPPING MARKS:</td>
        <td class="b-r">{{ trim($h->marca) }}</td>
    </tr>
    <tr>
        <td colspan="4" class="b-l">{{ trim($h->site) }}</td>
        <td>CONTACT:</td>
        <td class="b-r">{{ trim($h->contato) }}</td>
    </tr>
    <tr>
        <td colspan="4" class="b-l b-b"></td>
        <td class="b-b">EMAIL:</td>
        <td class="b-r b-b">{{ trim($h->email_contato) }}</td>
    </tr>

    {{-- Spacer --}}
    <tr><td colspan="6" style="height: 6px;"></td></tr>

    {{-- IMPORTER / BUYER + PURCHASE ORDER --}}
    <tr>
        <th colspan="4" class="th-header b-all" style="text-align: left;">IMPORTER / BUYER</th>
        <th colspan="2" class="th-header b-all">PURCHASE ORDER NUMBER</th>
    </tr>
    <tr>
        <td colspan="4" class="b-l">{{ trim($h->texto1_buyer) }}</td>
        <td colspan="2" rowspan="3" align="center" class="b-l b-r b-b" style="vertical-align: middle;">{{ trim($h->ordem) }}</td>
    </tr>
    <tr>
        <td colspan="4" class="b-l">{{ trim($h->texto2_buyer) }}</td>
    </tr>
    <tr>
        <td colspan="4" class="b-l">{{ trim($h->texto3_buyer) }}</td>
    </tr>
    <tr>
        <td colspan="4" class="b-l">{{ trim($h->texto4_buyer) }}</td>
        <td>CONTACT:</td>
        <td class="b-r">{{ trim($h->nom_contato) }}</td>
    </tr>
    <tr>
        <td colspan="4" class="b-l b-b">{{ trim($h->texto5_buyer) }}</td>
        <td class="b-b">EMAIL:</td>
        <td class="b-r b-b">{{ trim($h->email) }}</td>
    </tr>

    {{-- Spacer --}}
    <tr><td colspan="6" style="height: 6px;"></td></tr>

    {{-- INCOTERMS / CURRENCY / PAYMENT --}}
    <tr>
        <td class="b-t b-l">INCOTERMS 2010:</td>
        <td colspan="5" class="b-t b-r">{{ trim($h->cod_incoterm) }} - {{ trim($h->den_incoterms) }}</td>
    </tr>
    <tr>
        <td class="b-l">CURRENCY:</td>
        <td colspan="5" class="b-r">{{ trim($h->den_moeda) }}</td>
    </tr>
    <tr>
        <td class="b-b b-l">PAYMENT TERMS:</td>
        <td colspan="5" class="b-b b-r">{{ trim($h->cond_pgto_ingles) }}</td>
    </tr>

    {{-- Spacer --}}
    <tr><td colspan="6" style="height: 6px;"></td></tr>

    {{-- CONTAINER / SHIPPING DETAILS --}}
    <tr>
        <td class="b-t b-l">CONTAINER TYPE:</td>
        <td colspan="3" class="b-t b-r">40 REEFER HIGH CUBE</td>
        <td class="b-t">SHIPMENT PERIOD:</td>
        <td class="b-t b-r">{{ $prevEmbarque }}</td>
    </tr>
    <tr>
        <td class="b-l">CONTR TEMPERATURE:</td>
        <td colspan="3" class="b-r">MINUS {{ trim($h->cont_temperatura) }} CELSIUS DEGREES</td>
        <td>SHIPPING LINE:</td>
        <td class="b-r">to be informed</td>
    </tr>
    <tr>
        <td class="b-l">PORT OF LOADING:</td>
        <td colspan="3" class="b-r">{{ trim($h->local_embarque) }} - {{ trim($h->pais_int) }}</td>
        <td>Estimated TRANSIT TIME:</td>
        <td class="b-r">to be informed</td>
    </tr>
    <tr>
        <td class="b-l">PORT OF DISCHARGE:</td>
        <td colspan="3" class="b-r">{{ trim($h->local_destino) }}</td>
        <td>FREIGHT CONDITION:</td>
        <td class="b-r">{{ trim($h->frt_incoterms) }}</td>
    </tr>
    <tr>
        <td class="b-b b-l">FINAL DESTINATION:</td>
        <td colspan="3" class="b-b b-r">{{ trim($h->pais_destino) }}</td>
        <td colspan="2" class="b-b b-r"></td>
    </tr>

    {{-- Spacer --}}
    <tr><td colspan="6" style="height: 6px;"></td></tr>

    {{-- LEGALIZATION / HALAL / CERTIFICATES / INSPECTION --}}
    {{-- These widths define all 6 columns: 20% | 20% | 8% | 12% | 18% | 22% --}}
    <tr>
        <td class="b-t b-l" width="20%">LEGALIZATION:</td>
        <td class="b-t" width="20%">{{ trim($h->ies_legaliz) }}</td>
        <td class="b-t" width="8%">HALAL:</td>
        <td class="b-t b-r" width="12%">{{ trim($h->ies_halal) }}</td>
        <td class="b-t" width="18%">OTHER CERTIFICATES:</td>
        <td class="b-t b-r" width="22%">{{ trim($h->ies_certificates) }}</td>
    </tr>
    <tr>
        <td class="b-t b-b b-l">PRE-SHIPT INSPECTION:</td>
        <td class="b-t b-b">{{ trim($h->ies_insp_preshipt) }}</td>
        <td class="b-t b-b">PAID BY:</td>
        <td class="b-t b-b b-r">{{ trim($h->paid_by) }}</td>
        <td class="b-b">PAID BY:</td>
        <td class="b-b b-r">{{ trim($h->paid_by1) }}</td>
    </tr>

    {{-- Separator bar --}}
    <tr><td colspan="6" style="height: 6px;"></td></tr>

</table>

{{-- ══════════════════════════════════════════════════════════════
     TABLE 2: ITEMS + TOTALS + ADVANCED PAYMENT + SPECIAL REQUIREMENTS
     ══════════════════════════════════════════════════════════════ --}}
<table width="100%" cellspacing="0" cellpadding="2">
    <tr>
        <th class="th-header b-t b-l" align="center" style="white-space: nowrap;">CNTR</th>
        <th class="th-header b-t b-l" align="left" style="text-align: left;">PRODUCT DESCRIPTION</th>
        <th class="th-header b-t b-l" align="left" style="white-space: nowrap;">LABEL</th>
        <th class="th-header b-t b-l" align="center" style="white-space: nowrap;">PRICE PER TON</th>
        <th class="th-header b-t b-l" align="center" style="white-space: nowrap;">QUANTITY (TONS)</th>
        <th class="th-header b-t b-l b-r" align="center" style="white-space: nowrap;">AMOUNT</th>
    </tr>

    @foreach ($items as $item)
    <tr>
        <td align="center" class="b-t b-l" style="white-space: nowrap;">{{ trim($item->embarque) }}</td>
        <td class="b-t b-l" style="white-space: nowrap;">{{ trim($item->den_item_int) }}</td>
        <td align="left" class="b-t b-l" style="white-space: nowrap;">{{ trim($item->cod_item) }}</td>
        <td align="right" class="b-t b-l" style="white-space: nowrap;">{{ $item->den_moeda_abrev }} {{ number_format($item->val_tonelada, 2, '.', ',') }}</td>
        <td align="right" class="b-t b-l" style="white-space: nowrap;">{{ number_format($item->qtd_pecas_solic, 3, '.', ',') }}</td>
        <td align="right" class="b-t b-l b-r" style="white-space: nowrap;">{{ $item->den_moeda_abrev }} {{ number_format($item->val_tot_item, 2, '.', ',') }}</td>
    </tr>
    @endforeach

    {{-- TOTALS --}}
    <tr>
        <td colspan="3" align="right" class="b-t"></td>
        <td align="right" class="b-t b-r">TOTAL:</td>
        <th class="th-header b-all" style="text-align: right;">{{ number_format($totQtd, 3, '.', ',') }}</th>
        <th class="th-header b-all" style="text-align: right;">{{ $denMoeda }} {{ number_format($totVal, 2, '.', ',') }}</th>
    </tr>

    {{-- Spacer --}}
    <tr><td colspan="6" style="height: 6px;"></td></tr>

    {{-- ADVANCED PAYMENT --}}
    <tr>
        <td colspan="3"></td>
        <td colspan="2" align="right">ADVANCED PAYMENT: {{ $h->pct_adiant ?? 0 }} %</td>
        <th class="th-header" style="text-align: right;">{{ $denMoeda }} {{ number_format($adiantamento, 2, '.', ',') }}</th>
    </tr>

    {{-- Spacer --}}
    <tr><td colspan="6" style="height: 6px;"></td></tr>

    {{-- SPECIAL REQUIREMENTS --}}
    <tr>
        <th colspan="2" class="th-header b-all" style="text-align: left;">SPECIAL REQUIREMENTS</th>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2" class="b-l b-r b-b" style="min-height: 17px;">{{ trim($h->ref_cliente_final) }}</td>
        <td></td>
        <td></td>
    </tr>

    {{-- Spacer --}}
    <tr><td colspan="6" style="height: 6px;"></td></tr>

</table>

{{-- ══════════════════════════════════════════════════════════════
     TABLE 3: BANK DETAILS + CONTRACT TERMS AND CONDITIONS
     ══════════════════════════════════════════════════════════════ --}}
<table width="100%" cellspacing="0" cellpadding="2">

    {{-- BANK DETAILS --}}
    <tr>
        <th colspan="2" class="th-header b-all" style="text-align: left;">{{ trim($h->detalhe_empresa) }}</th>
        <td colspan="4" class="b-t b-r"><b>{{ trim($h->banco) }}</b></td>
    </tr>
    <tr><td colspan="6" class="b-l b-r">{{ trim($h->banco1) }}</td></tr>
    <tr><td colspan="6" class="b-l b-r">{{ trim($h->banco3) }}</td></tr>
    <tr><td colspan="6" class="b-l b-r">{{ trim($h->banco4) }}</td></tr>
    <tr><td colspan="6" class="b-l b-r">{{ trim($h->banco2) }}</td></tr>
    <tr><td colspan="6" class="b-l b-r">{{ trim($h->banco5) }}</td></tr>
    <tr><td colspan="6" class="b-l b-r">{{ trim($h->banco6) }}</td></tr>
    <tr><td colspan="6" class="b-l b-r b-b">{{ trim($h->banco7) }}</td></tr>

    {{-- Spacer --}}
    <tr><td colspan="6" style="height: 6px;"></td></tr>

    {{-- CONTRACT TERMS AND CONDITIONS --}}
    <tr>
        <th colspan="2" class="th-header b-all" style="text-align: left;">CONTRACT TERMS AND CONDITIONS</th>
        <td colspan="4" class="b-t b-r"><b>{{ $h->proforma }}</b></td>
    </tr>

    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>1 -</b> It is mandatory return by email this Proforma duly signed/stamped by your company within <b>02 working days maximum</b>;</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>2 -</b> Full mandatory Documental Instructions MUST be sent us by email <u>immediately</u> after receiving this Proforma;</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>3 -</b> Containers to Russian Federation, Jordan and Lebanon MUST have temperature of <b>- {{ trim($h->cont_temperatura) }}&deg;C</b>. For other destinations, container temperature should be <b>- {{ trim($h->cont_temperatura) }}&deg;C</b>;</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>4 -</b> Any discount asked by importer/buyer will be deducted from the trading's commission;</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>5 -</b> After receiving the document copies for checking, <u>approval</u> must be given <b>within 2 working days maximum</b>. Any changing request on docs after shipping line's deadlines for B/L draft MUST be paid by the <b>buyer/importer</b>;</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>6 -</b> Advanced Payments MUST be done <u>immediately</u> after receiving the "Shipment Schedule" from us. Containers WILL NOT be stuffed without this payment confirmation.<br>Advanced payments will not be refundable if order canceled by Buyers/Importers after 5 days of signature confirmation;</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>7 -</b> Payments on the modality <b>T/T (against faxed copies of docs - Wire transference) MUST</b> be done <u>in the maximum</u> of <b>10 days after vessel departure from Port of Origin</b>. Original documentation will be sent abroad <u>only after</u> this payment confirmation;</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>8 -</b> Payments on the modality <b>CAD / DP at sight (cash against of documents) MUST</b> be done <u>until</u> the vessel arrival at destination;</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>9 -</b> <u>All costs at destination</u> caused by delaying on payment or container clearance will be on <b><u>Buyer/Importer's account</u></b>;</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>10 -</b> <u>Shipments to Hong Kong</u>: we have the standard 05 days of free demurrage/05 days of free detention at HK Port;</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>11 -</b> On FOB and CFR sales, we do NOT accept any bookings done with PORTLINE/Action Cargo;</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>12 -</b> "Transit Times" of shipping lines generally may vary during the voyage, so we are not responsible for casual problems this change may cause;</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>13 -</b> "Shipment Period" may vary, but any change will be informed by us to importer/buyer;</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>14 -</b> Any change on quantity of product during stuffing will be duly informed by us to importer/buyer;</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>15 -</b> <b>**<u>Important</u>** All Payments <u>MUST</u></b> be done by the same person/company mentioned on <b>Invoice</b> and <u>MUST</u> be paid the <b>exact amount</b> (including cents) because of Brazilian Customs Law;</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>16 -</b> This Proforma follows the INCOTERMS 2010 published by International Chamber of Commerce(ICC), Zurich, Swiss, (Publication nr: 715, 2010).</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>17 -</b> This contract is binding and will be honoured by both parties Shipper and Importer. Whether the prices go up or down both parties will honour their commitment. In case for any unknown reason the shipment is not effected during the agreed period then Shipper will ask a written confirmation from the client to reconfirm the new dates.</p>
        </td>
    </tr>
    <tr class="clause">
        <td colspan="6" class="b-l b-r justified">
            <p><b>18 -</b> The importer acknowledges and agrees that it is his responsibility to verify the authenticity of all international health certificates received from the exporting company. This check must be carried out immediately upon receipt of the documents. The importer undertakes to implement appropriate measures to ensure that each international health certificate is subject to thorough verification. The importer acknowledges that failure to properly verify certificates may result in contractual penalties, including, but not limited to, temporary suspension of commercial transactions and reevaluation of the commercial relationship. Both parties agree to fully collaborate to ensure the integrity of certification processes and promote a secure and reliable supply chain.</p>
        </td>
    </tr>
    <tr>
        <td colspan="6" class="b-l b-r" style="height: 15px;"></td>
    </tr>

</table>

{{-- ══════════════════════════════════════════════════════════════
     TABLE 4: SIGNATURE SECTION
     ══════════════════════════════════════════════════════════════ --}}
<table width="100%" cellspacing="0" cellpadding="2">
    <tr>
        <td width="40%" align="center" class="b-l"></td>
        <td width="20%" align="center"></td>
        <td width="40%" align="center" class="b-r">
            @if(file_exists($ass))
                <img src="{{ $ass }}" alt="Assinatura" width="180">
            @endif
        </td>
    </tr>
    <tr>
        <td align="center" class="b-l" style="height: 10px;"></td>
        <td></td>
        <td align="center" class="b-r"></td>
    </tr>
    <tr>
        <td align="center" class="b-t b-l"><b>{{ trim($h->texto1_buyer) }}</b></td>
        <td></td>
        <td align="center" class="b-t b-r"><b>{{ trim($h->den_empresa) }}</b></td>
    </tr>
    <tr>
        <td align="center" class="b-l">Signature & Stamp</td>
        <td></td>
        <td align="center" class="b-r">{{ trim($h->contato) }}</td>
    </tr>
    <tr>
        <td align="center" class="b-l"></td>
        <td></td>
        <td align="center" class="b-r"><b>Export Department</b></td>
    </tr>
    <tr>
        <td align="center" class="b-l">By signing the Buyer/Importer declares agreement on all terms.</td>
        <td></td>
        <td align="center" class="b-r" style="padding-top: 8px;">
            @if(file_exists($logo))
                <img src="{{ $logo }}" alt="Logo" width="55">
            @endif
            &nbsp;&nbsp;&nbsp;
            @if(file_exists($sif))
                <img src="{{ $sif }}" alt="SIF" width="40">
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="3" class="b-l b-r" style="height: 10px;"></td>
    </tr>
    <tr>
        <td colspan="3" class="b-b b-l b-r" style="text-align: center;"><b>{{ $datInclusao }}</b></td>
    </tr>
</table>

</body>
</html>

@php
    use Carbon\Carbon;

    // --- Branding ---
    $bg   = $branding['bg_color'];
    $fc   = $branding['font_color'];
    $logo = public_path($branding['logo']);
    $ass  = public_path($branding['signature']);

    $h = $header;

    // --- Current date (ISF uses current date, not embarque) ---
    $datAtual = Carbon::now()->locale('en')->isoFormat('MMMM D, YYYY');

    $empresa   = trim($h->den_razao_social ?? '');
    $endereco  = trim($h->end_empresa ?? '');
    $municipio = trim($h->den_munic ?? '');
    $uf        = trim($h->den_uni_feder ?? '');
    $paisEmp   = trim($h->pais_emp ?? '');
    $paisInt   = trim($h->pais_int ?? '');
    $sif       = trim($h->sif ?? '');
    $cep       = trim($h->cod_cep ?? '');

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
    <title>ISF 10+2 {{ trim($h->proforma) }}-{{ trim($h->embarque) }}</title>
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
     FIXED FOOTER
     ══════════════════════════════════════════════════════════════ --}}
<div class="page-footer">
    <table cellspacing="0" cellpadding="3">
        <tr>
            <td colspan="3" class="b-b"></td>
        </tr>
        <tr>
            <td width="15%"></td>
            <td width="70%" align="center"><b>{{ $endereco }}</b></td>
            <td width="15%"></td>
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
        <th align="center" style="font-size: 16px; font-weight: bold;">ISF 10+2</th>
    </tr>
    <tr>
        <th align="center">{{ trim($h->proforma ?? '') }}-{{ trim($h->embarque ?? '') }}</th>
    </tr>
    <tr>
        <td class="b-b"></td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 3: PAGE + SUBTITLE
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <td width="25%">PAGE.: 1</td>
        <td>ISF 10+2</td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 4: ISF DETAILS
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr><td style="height: 10px;"></td></tr>
    <tr>
        <td><b>INVOICE NR</b>: {{ trim($h->proforma ?? '') }}-{{ trim($h->embarque ?? '') }}</td>
    </tr>
    <tr>
        <td><b>CONTRACT NR</b>: {{ trim($h->ordem ?? '') }}</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
    <tr>
        <td align="center">{{ $municipio }}, {{ $datAtual }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <td><b>SELLER</b>: {{ $empresa }}, CNPJ: {{ trim($h->num_cgc ?? '') }}, {{ $endereco }}, {{ $municipio }} - {{ $uf }} - {{ $paisEmp }}, TEL: {{ trim($h->num_telefone ?? '') }} - FAX: {{ trim($h->num_fax ?? '') }}</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
    <tr>
        <td><b>BUYER NOTIFY</b>: {{ trim($h->texto1_consignat ?? '') }} {{ trim($h->texto2_consignat ?? '') }} {{ trim($h->texto3_consignat ?? '') }}</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
    <tr>
        <td><b>VESSEL</b>: {{ trim($h->den_navio_aviao ?? '') }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <td><b>SHIPPING LINE</b>: {{ trim($h->armador ?? '') }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <td><b>CONTAINERS</b>: {{ trim($h->cod_container ?? '') }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <td><b>BILL OF LOADING NUMBER</b>: {{ trim($h->num_bl ?? '') }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <td><b>VOYAGE NUMBER</b>: {{ trim($h->voyage_number ?? '') }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <td><b>LOADING PORT</b>: {{ trim($h->local_embarque ?? '') }} - {{ $paisInt }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <td><b>SAIL DATE</b>: {{ trim($h->dat_etd ?? '') }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <td><b>DESTINATION PORT</b>: {{ trim($h->local_destino ?? '') }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <td><b>ARRIVAL DATE</b>: {{ trim($h->dat_eta ?? '') }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <td><b>MANUFACTURE (OR SUPPLIER)</b>: {{ $empresa }}, {{ $endereco }} - {{ $municipio }} - {{ $uf }} - {{ $paisEmp }} - CEP: {{ $cep }} - {{ $sif }}</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
    <tr>
        <td><b>COUNTRY OF ORIGIN</b>: {{ $paisInt }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <td><b>COMMODITY HARMONIZED TARIFF SCHEDULE OF THE UNITED STATES (HTSUS) NUMBER</b>: {{ $ncm ?? '' }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <td><b>COMMODITY DESCRIPTION</b>: {{ trim($h->den_item_int ?? '') }}</td>
    </tr>
    <tr><td style="height: 5px;"></td></tr>
    <tr>
        <td><b>CONTAINER STUFFING LOCATION</b>: {{ $empresa }}, {{ $endereco }}, {{ $municipio }} - {{ $uf }} - {{ $paisEmp }} - CEP: {{ $cep }} - {{ $sif }}</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
    <tr>
        <td><b>IMPORT PERMIT NR</b>: {{ trim($h->import_permit ?? '') }}</td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 5: SIGNATURE
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="2">
    <tr><td style="height: 30px;"></td></tr>
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

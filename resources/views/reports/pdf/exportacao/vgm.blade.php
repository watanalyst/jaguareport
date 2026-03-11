@php
    use Carbon\Carbon;

    // --- Branding ---
    $bg   = $branding['bg_color'];
    $fc   = $branding['font_color'];
    $logo = public_path($branding['logo']);
    $ass  = public_path($branding['signature']);

    $h = $header;

    // --- Date Formatting ---
    $datAtual = $h->dat_atual ? Carbon::parse($h->dat_atual)->locale('en')->isoFormat('MMMM D, YYYY') : '';

    $empresa  = trim($h->den_razao_social ?? '');
    $endereco = trim($h->end_empresa ?? '');
    $municipio = trim($h->den_munic ?? '');
    $uf       = trim($h->den_uni_feder ?? '');
    $paisEmp  = trim($h->pais_emp ?? '');

    $taraCont     = (float) ($h->tara_cont ?? 0);
    $rateioPalete = (float) ($h->rateio_palete ?? 0);
    $vgm          = (float) ($h->vgm ?? 0);

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
    <title>VGM {{ trim($h->proforma) }}-{{ trim($h->embarque) }}</title>
    <style>
        @page { margin: 30px 25px 60px 25px; }

        * { font-family: Helvetica, Arial, sans-serif; font-size: 10px; line-height: 1.3; }

        body { margin: 0; padding: 0; color: #333; }

        table { border-collapse: separate; border-spacing: 0; width: 100%; }

        th, td { vertical-align: top; padding: 2px 4px; font-size: 12px; }

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
        <th align="center" style="font-size: 16px; font-weight: bold;">VGM - VERIFIED GROSS MASS</th>
    </tr>
    <tr>
        <th align="center">{{ trim($h->proforma ?? '') }}-{{ trim($h->embarque ?? '') }}</th>
    </tr>
    <tr>
        <td class="b-b"></td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 3: DATE + SHIPPER
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <th align="right">{{ $datAtual }}</th>
    </tr>
    <tr>
        <th align="left">SHIPPER:</th>
    </tr>
    <tr>
        <td align="left">{{ $empresa }}</td>
    </tr>
    <tr>
        <td align="left">{{ $endereco }}</td>
    </tr>
    <tr>
        <td align="left">{{ $municipio }} - {{ $uf }} - {{ $paisEmp }}</td>
    </tr>
    <tr><td style="height: 15px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 4: BOOKING + CONTAINER GRID
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <td colspan="4"><strong>BOOKING:</strong> {{ trim($h->num_bl ?? '') }}</td>
    </tr>
    <tr>
        <th class="th-header b-t b-l" style="text-align:left;">CONTAINER NUMBER</th>
        <th align="center" class="th-header b-t b-l">TARE</th>
        <th align="center" class="th-header b-t b-l">CARGO WEIGHT</th>
        <th align="center" class="th-header b-t b-l b-r">VGM (TARE + CARGO WEIGHT)</th>
    </tr>
    <tr>
        <th align="left" class="b-t b-b b-l">{{ trim($h->cod_container ?? '') }}</th>
        <th align="right" class="b-t b-b b-l">{{ number_format($taraCont, 2, ',', '.') }}</th>
        <th align="right" class="b-t b-b b-l">{{ number_format($rateioPalete, 2, ',', '.') }} KG</th>
        <th align="right" class="b-t b-b b-l b-r">{{ number_format($vgm, 2, ',', '.') }} KG</th>
    </tr>
    <tr><td style="height: 15px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 5: METHOD
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <th align="left">METHOD:</th>
    </tr>
    <tr>
        <td align="left"><span style="display:inline-block;width:10px;height:10px;border:1px solid #000;margin-right:4px;vertical-align:middle;"></span> 1 - Packed and Sealed Total Container Weight</td>
    </tr>
    <tr>
        <td align="left"><span style="display:inline-block;width:10px;height:10px;border:1px solid #000;margin-right:4px;vertical-align:middle;text-align:center;font-size:8px;line-height:10px;font-weight:bold;">X</span> 2 - Weight of Cargo with Packing Materials + Tare Weight of the Container</td>
    </tr>
    <tr><td style="height: 15px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 6: AUTHORIZED PERSON
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <th width="30%" align="left" class="b-t b-l">AUTORIZED PERSON NAME:</th>
        <td width="70%" align="left" class="b-t b-l b-r">Victor Hugo Alves</td>
    </tr>
    <tr>
        <th align="left" class="b-t b-l">COMPANY:</th>
        <td align="left" class="b-t b-l b-r">{{ $empresa }}</td>
    </tr>
    <tr>
        <th align="left" class="b-t b-l">PHONE:</th>
        <td align="left" class="b-t b-l b-r">+55 49 3449-1300</td>
    </tr>
    <tr>
        <th align="left" class="b-t b-l">EMAIL:</th>
        <td align="left" class="b-t b-l b-r">victor.alves@grupobtz.com.br</td>
    </tr>
    <tr>
        <th align="left" class="b-t b-b b-l">DATE:</th>
        <td align="left" class="b-t b-b b-l b-r">{{ $datAtual }}</td>
    </tr>
    <tr><td style="height: 30px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 7: SIGNATURE
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

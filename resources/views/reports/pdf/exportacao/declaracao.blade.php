@php
    use Carbon\Carbon;

    // --- Branding ---
    $bg   = $branding['bg_color'];
    $fc   = $branding['font_color'];
    $logo = public_path($branding['logo']);
    $ass  = public_path($branding['signature']);

    $h = $header;

    // --- Current date in Portuguese ---
    $mesesPT = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];
    $now = Carbon::now();
    $datAtual = $now->day . ' de ' . $mesesPT[$now->month] . ' de ' . $now->year;

    $empresa   = trim($h->den_razao_social ?? '');
    $municipio = trim($h->den_munic ?? '');
    $container = trim($h->cod_container ?? '');
    $referSif  = trim($h->refer_sig_sif ?? '');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Declaração {{ trim($h->proforma) }}-{{ trim($h->embarque) }}</title>
    <style>
        @page { margin: 30px 25px 60px 25px; }

        * { font-family: Helvetica, Arial, sans-serif; line-height: 1.3; }

        body { margin: 0; padding: 0; color: #333; font-size: 10.5px; }

        table { border-collapse: separate; border-spacing: 0; width: 100%; }

        th, td { vertical-align: top; padding: 2px 4px; font-size: 10.5px; }

        .tbl-lg td, .tbl-lg th { font-size: 14px; }

        .b-b { border-bottom: 1px solid #000; }

        p { margin: 4px 0; text-align: justify; }

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
            <td width="70%" align="center"><b>{{ $empresa }}</b></td>
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
            {{-- DEC não usa stamp (copy_original: false) --}}
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
        <th align="center" style="font-size: 16px; font-weight: bold;">DECLARAÇÃO</th>
    </tr>
    <tr>
        <th align="center">{{ trim($h->proforma ?? '') }}-{{ trim($h->embarque ?? '') }}</th>
    </tr>
    <tr>
        <td class="b-b"></td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 3: CORPO DA DECLARAÇÃO (fonte 14px)
     ══════════════════════════════════════════════════════════════ --}}
<table class="tbl-lg" cellspacing="0" cellpadding="3">
    <tr><td colspan="3" style="height: 30px;"></td></tr>
    <tr>
        <td width="10%"></td>
        <td width="80%" align="center">Certificamos que as aves abatidas pela {{ $empresa }} obedecem aos seguintes requisitos:</td>
        <td width="10%"></td>
    </tr>
    <tr><td colspan="3" style="height: 30px;"></td></tr>
    <tr>
        <td></td>
        <td>
            <p>&#8226; Ausência de substâncias químicas indesejáveis e ilegais, antibióticos e cloranfenicol;</p>
            <p>&#8226; Níveis de dioxinas dentro dos parâmetros estabelecidos pela legislação vigente;</p>
            <p>&#8226; Provêm de estabelecimento de criação isentos de quaisquer medidas de defesa sanitária relacionadas com doenças aviárias, consideradas notificáveis pelas Autoridades Veterinárias Brasileira. Em torno dos quais, num raio de 50 km, não se registrou foco de Peste Aviária ou Doença de Newcastle, nos últimos 90 dias. Não foram abatidas no âmbito de qualquer programa sanitário para o controle ou erradicação de doenças aviárias. Durante o transporte para o matadouro não estiveram em contato com aves que apresentaram sinais de Peste Aviária ou de Doença de Newcastle;</p>
            <p>&#8226; Livre de hormônios;</p>
            <p>&#8226; Livre da utilização de Nitrofurano na ração;</p>
            <p>&#8226; As aves, das quais foi obtida a carne, não foram submetidas à ação de substâncias hormonais ou transgênicas, naturais ou sintéticas, de preparados tireostáticos, antibióticos, pesticidas e outras substâncias medicamentosas, aplicados antes do abate por prazos superiores aos recomendados pelas instruções de seu uso;</p>
            <p>&#8226; Participam do programa de controle de Salmonella spp a campo, conforme Programa Nacional de Sanidade Avícola (PNSA).</p>
        </td>
        <td></td>
    </tr>
    <tr><td colspan="3" style="height: 15px;"></td></tr>
    <tr>
        <td></td>
        <td align="right"><b>{{ $municipio }}:</b> {{ $datAtual }}</td>
        <td></td>
    </tr>
    <tr><td colspan="3" style="height: 15px;"></td></tr>
    <tr>
        <td></td>
        <th align="left">Certificado Sanitário Internacional</th>
        <td></td>
    </tr>
    <tr><td colspan="3" style="height: 10px;"></td></tr>
    <tr>
        <td></td>
        <th align="left">CONTAINER: {{ $container }}</th>
        <td></td>
    </tr>
    <tr><td colspan="3" style="height: 10px;"></td></tr>
    <tr>
        <td></td>
        <th align="left">REFERÊNCIA SIGSIF: {{ $referSif }}</th>
        <td></td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 4: SIGNATURE
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

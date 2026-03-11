@php
    use Carbon\Carbon;

    // --- Branding ---
    $bg   = $branding['bg_color'];
    $fc   = $branding['font_color'];
    $logo = public_path($branding['logo']);

    $h = $header;

    // --- Current date ---
    $datAtual = Carbon::now()->locale('en')->isoFormat('MMMM D, YYYY');

    $empresa     = trim($h->den_razao_social ?? '');
    $paisDestino = strtoupper(trim($h->pais_destino ?? ''));
    $numHalal    = trim($h->num_halal ?? '');
    $hcCsi       = trim($h->ies_csi_dsc ?? '');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Protocolo {{ trim($h->proforma) }}-{{ trim($h->embarque) }}</title>
    <style>
        @page { margin: 30px 25px 60px 25px; }

        * { font-family: Helvetica, Arial, sans-serif; line-height: 1.3; }

        body { margin: 0; padding: 0; color: #333; font-size: 10.5px; }

        table { border-collapse: separate; border-spacing: 0; width: 100%; }

        th, td { vertical-align: top; padding: 2px 4px; font-size: 10.5px; }

        .tbl-lg td, .tbl-lg th { font-size: 14px; }
        .tbl-xl td, .tbl-xl th { font-size: 16px; }

        .b-b { border-bottom: 1px solid #000; }

        .b-all { border: 1px solid #000; }

        .b-lr { border-left: 1px solid #000; border-right: 1px solid #000; }

        .b-tlr { border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; }

        .b-blr { border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; }

        .red { color: #ff0000; }

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
            {{-- PRO não usa stamp (copy_original: false) --}}
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

@if($paisDestino)
{{-- ══════════════════════════════════════════════════════════════
     SECTION 2: TITLE
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="2">
    <tr>
        <th align="center" style="font-size: 16px; font-weight: bold;">PROTOCOLO</th>
    </tr>
    <tr>
        <th align="center">{{ trim($h->proforma ?? '') }}-{{ trim($h->embarque ?? '') }}</th>
    </tr>
    <tr>
        <td class="b-b"></td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 3a: DATA + SAUDAÇÃO
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <td align="right">{{ $datAtual }}</td>
    </tr>
    <tr>
        <td>À</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 3b: DESTINATÁRIO (fonte 14px)
     ══════════════════════════════════════════════════════════════ --}}
<table class="tbl-xl" cellspacing="0" cellpadding="3">
    <tr>
        <td><b>SeaRed Acessoria Documental Ltda.</b></td>
    </tr>
    <tr>
        <td><b>Av. Nova Cantareira n.: 291 Sala 102.</b></td>
    </tr>
    <tr>
        <td><b>Alto de Santana - Cep.: 02331 - 000.</b></td>
    </tr>
    <tr>
        <td><b>São Paulo - SP.</b></td>
    </tr>
    <tr>
        <td><b>Tels.: 11 2206.1116 / 2206.1117.</b></td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
    <tr>
        <td>A/C Lucas/Danilo/Priscila</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
</table>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 4: FATURA + DOCUMENTOS (fonte 14px)
     ══════════════════════════════════════════════════════════════ --}}
<table class="tbl-lg" cellspacing="0" cellpadding="3">
    <tr>
        <th align="center" class="b-all">FATURA: <span class="red">{{ trim($h->proforma ?? '') }}-{{ trim($h->embarque ?? '') }}</span> HALAL <span class="red">{{ $numHalal }}</span></th>
    </tr>
    <tr>
        <td>SEGUE EM ANEXO OS SEGUINTES DOCUMENTOS:</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
</table>

@if($paisDestino === 'LIBIA')
{{-- LÍBIA: checklist com reconhecimento de firma --}}
<table class="tbl-lg" cellspacing="0" cellpadding="3">
    <tr>
        <td class="b-tlr" style="height: 5px;"></td>
    </tr>
    <tr>
        <th align="left" class="b-lr">&raquo; ( ) INVOICE, 01 ORIGINAL</th>
    </tr>
    <tr>
        <th align="left" class="b-lr">&raquo; ( ) CERTIFICADO SANITÁRIO <span class="red">{{ $hcCsi }}</span> 01 ORIGINAL</th>
    </tr>
    <tr>
        <th align="left" class="b-lr">&raquo; ( ) HALAL</th>
    </tr>
    <tr>
        <th align="left" class="b-lr">&raquo; ( ) RECONHECER FIRMA DE TODOS DOCUMENTOS</th>
    </tr>
    <tr>
        <td class="b-blr" style="height: 5px;"></td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
</table>

<table class="tbl-lg" cellspacing="0" cellpadding="3">
    <tr>
        <td>FAVOR ENVIAR DOCUMENTOS PARA LEGALIZAR NA CÂMARA ÁRABE DE COMÉRCIO E EMBAIXADA DA <b class="red">{{ $paisDestino }}</b>.</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
</table>
@else
{{-- DEMAIS PAÍSES: checklist padrão --}}
<table class="tbl-lg" cellspacing="0" cellpadding="3">
    <tr>
        <td class="b-tlr" style="height: 5px;"></td>
    </tr>
    <tr>
        <th align="left" class="b-lr">&raquo; ( ) INVOICE, 01 ORIGINAL</th>
    </tr>
    <tr>
        <th align="left" class="b-lr">&raquo; ( ) CERTIFICADO SANITÁRIO <span class="red">{{ $hcCsi }}</span> 01 ORIGINAL</th>
    </tr>
    <tr>
        <th align="left" class="b-lr">&raquo; ( ) HALAL</th>
    </tr>
    <tr>
        <td class="b-blr" style="height: 5px;"></td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
</table>

{{-- Instrução específica por país --}}
<table class="tbl-lg" cellspacing="0" cellpadding="3">
    @if($paisDestino === 'UNITED ARAB EMIRATES')
        <tr>
            <td>FAVOR ENVIAR DOCUMENTOS PARA SEREM ATESTADOS NO CONSULADO UAE</td>
        </tr>
        <tr>
            <td>&raquo; Embaixada: <b class="red">{{ $paisDestino }}</b></td>
        </tr>
    @elseif($paisDestino === 'QATAR')
        <tr>
            <td>FAVOR ENVIAR DOCUMENTOS PARA SEREM LEGALIZADOS NA CÂMARA ÁRABE- CCAB</td>
        </tr>
        <tr>
            <td>&raquo; Embaixada: <b class="red">{{ $paisDestino }}</b></td>
        </tr>
    @elseif($paisDestino === 'OMAN')
        <tr>
            <td>FAVOR ENVIAR DOCUMENTOS PARA SEREM LEGALIZADOS NA CÂMARA ÁRABE, cartório, Itamaraty e embaixada de Omã</td>
        </tr>
        <tr>
            <td>&raquo; Embaixada: <b class="red">{{ $paisDestino }}</b></td>
        </tr>
    @elseif($paisDestino === 'KUWAIT')
        <tr>
            <td>FAVOR ENVIAR DOCUMENTOS PARA SEREM LEGALIZADOS NA CÂMARA ÁRABE E EMBAIXADA - EMBAIXADA DO KUWAIT</td>
        </tr>
        <tr>
            <td>&raquo; Embaixada: <b class="red">{{ $paisDestino }}</b></td>
        </tr>
    @elseif($paisDestino === 'JORDAN')
        <tr>
            <td>FAVOR ENVIAR DOCUMENTOS PARA SEREM LEGALIZADOS NA CÂMARA ÁRABE - e no consulado da Jordânia</td>
        </tr>
        <tr>
            <td>&raquo; Embaixada: <b class="red">{{ $paisDestino }}</b></td>
        </tr>
    @elseif($paisDestino === 'SAUDI ARABIA')
        <tr>
            <td>FAVOR ENVIAR DOCUMENTOS PARA SEREM LEGALIZADOS NA CÂMARA ÁRABE- CCAB</td>
        </tr>
        <tr>
            <td>&raquo; Embaixada: <b class="red">{{ $paisDestino }}</b></td>
        </tr>
    @elseif($paisDestino === 'LIBANO')
        <tr>
            <td>FAVOR ENVIAR DOCUMENTOS PARA SEREM LEGALIZADOS NA CÂMARA ÁRABE- CCAB</td>
        </tr>
        <tr>
            <td>&raquo; Câmara: <b class="red">{{ $paisDestino }}</b></td>
        </tr>
    @elseif($paisDestino === 'IRAQ')
        <tr>
            <td>FAVOR ENVIAR DOCUMENTOS PARA SEREM LEGALIZADOS NA CAMARA DO IRAQUE, cartório, Itamaraty e embaixada do Iraque</td>
        </tr>
        <tr>
            <td>&raquo; Câmara: <b class="red">{{ $paisDestino }}</b></td>
        </tr>
    @elseif($paisDestino === 'BAHRAIN')
        <tr>
            <td>FAVOR ENVIAR DOCUMENTOS PARA SEREM ATESTADOS NA CCAB, NÃO PRECISA LEGALIZAR - <b class="red">{{ $paisDestino }}</b>.</td>
        </tr>
    @endif
    <tr><td style="height: 10px;"></td></tr>
</table>
@endif

{{-- ══════════════════════════════════════════════════════════════
     SECTION 5: ASSINATURA / CONTATO
     ══════════════════════════════════════════════════════════════ --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <td>Atenciosamente,</td>
    </tr>
    <tr><td style="height: 10px;"></td></tr>
    <tr>
        <td>Departamento de Exportação</td>
    </tr>
    <tr>
        <td><u>bruna.santos@grupobtz.com.br</u> (Bruna Santos)</td>
    </tr>
    <tr>
        <td><u>thaila.ribeiro@grupobtz.com.br</u> (Thaila Ribeiro)</td>
    </tr>
</table>
@endif

</body>
</html>



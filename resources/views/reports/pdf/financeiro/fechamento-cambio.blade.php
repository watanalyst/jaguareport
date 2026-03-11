@extends('reports.pdf.layout')

@section('title', 'Fechamento Câmbio')

@section('styles')
<style>
    @page {
        size: A4 landscape !important;
        margin: 15px 20px 15px 20px !important;
    }

    header, footer {
        display: none !important;
        position: static !important;
        height: 0 !important;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 9px;
    }

    td, th {
        white-space: nowrap;
        overflow: hidden;
        padding: 1px 3px;
    }

    .tr-size {
        font-size: 8px;
    }

    .font-size-dezoito {
        font-size: 18px;
    }

    .thcolor {
        background-color: #d3d3d3;
    }

    .page-break {
        page-break-before: always;
    }

    .no-page-break {
        page-break-inside: avoid;
    }

    .borda-divisao {
        border-bottom: 2px solid #000;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    .border-all {
        border: 1px solid #000;
    }

    .border-bottom-lr {
        border-bottom: 1px solid #000;
        border-left: 1px solid #000;
        border-right: 1px solid #000;
    }

    .border-lr {
        border-left: 1px solid #000;
        border-right: 1px solid #000;
    }

    .border-top-right {
        border-top: 1px solid #000;
        border-right: 1px solid #000;
    }

    .border-top-lr {
        border-top: 1px solid #000;
        border-left: 1px solid #000;
        border-right: 1px solid #000;
    }

    .border-bottom-right {
        border-bottom: 1px solid #000;
        border-right: 1px solid #000;
    }

    .wrap-cell {
        white-space: normal;
        word-wrap: break-word;
    }
</style>
@endsection

@section('content')
@foreach ($sequencias as $index => $seq)
@php
    $p = $seq->primeiro;
    $datCambio = !empty($p->dat_cambio) ? \Carbon\Carbon::parse($p->dat_cambio)->format('d/m/Y') : '';
    $datAtual = \Carbon\Carbon::now()->format('d/m/Y H:i:s');
@endphp

<div @if($index > 0) class="page-break" @endif>

    {{-- Header --}}
    <table width="100%" cellspacing="0" cellpadding="1">
        <tr>
            <td width="25%" align="left">
                <img src="{{ public_path('img/logo_jagua.png') }}" style="height: 30px;">
            </td>
            <th width="50%" align="center" class="font-size-dezoito">FECHAMENTO CÂMBIO</th>
            <td width="25%" align="right">{{ $datAtual }}</td>
        </tr>
    </table>

    {{-- Banco e descrição --}}
    <table width="100%" cellspacing="0" cellpadding="1">
        <tr height="3"><td></td></tr>
        <tr>
            <th colspan="3" align="left">Ao BANCO {{ $p->bank ?? '' }}</th>
        </tr>
        <tr>
            <td colspan="1" align="left" class="wrap-cell">Conforme contato telefônico, solicitamos o fechamento de câmbio para as ordens descritas abaixo:</td>
            <td align="right">Data Câmbio: <b>{{ $datCambio }}</b></td>
            <th align="right">Seq.: {{ $seq->num_seq }}</th>
        </tr>
        <tr>
            <th colspan="3" align="center" class="border-all thcolor">BANCO {{ $p->bank ?? '' }}</th>
        </tr>
        <tr>
            <td colspan="3" align="center" class="border-bottom-lr tr-size">Auto-Atendimento</td>
        </tr>
        <tr>
            <td colspan="3" align="center" class="border-bottom-lr tr-size">Ordens de pagamento do exterior - Consulta</td>
        </tr>
    </table>

    {{-- Info empresa --}}
    <table width="100%" cellspacing="0" cellpadding="1">
        <tr height="5"><td></td></tr>
        <tr>
            <th colspan="4" class="border-all thcolor" height="20"></th>
        </tr>
        <tr>
            <th width="70" align="center" class="border-bottom-lr">Agência:</th>
            <th width="70" align="center">{{ $p->agencia ?? '' }}</th>
            <th align="left" class="border-lr">{{ $p->den_empresa ?? '' }}</th>
            <th width="115" align="center" style="border-right: 1px solid #000;">TOTAL {{ $p->moeda ?? '' }}</th>
        </tr>
        <tr>
            <th align="center" class="border-bottom-lr">Conta:</th>
            <th align="center" style="border-bottom: 1px solid #000;">{{ $p->conta ?? '' }}</th>
            <th align="left" class="border-bottom-lr">CNPJ: {{ $p->cnpj ?? '' }}</th>
            <th align="center" class="border-bottom-right">{{ number_format($seq->total_us, 2, ',', '.') }}</th>
        </tr>
        <tr>
            <th colspan="4" align="center">ORDENS DE PAGAMENTO</th>
        </tr>
    </table>

    {{-- Tabela de dados --}}
    <table width="100%" cellspacing="0" cellpadding="1">
        <tr>
            <th class="border-top-lr tr-size thcolor">Nº OPE</th>
            <th class="border-top-right tr-size thcolor">Inc.Créd.</th>
            <th class="border-top-right tr-size thcolor">NF</th>
            <th class="border-top-right tr-size thcolor">Data NF</th>
            <th class="border-top-right tr-size thcolor">DUE</th>
            <th class="border-top-right tr-size thcolor">Chave Acesso</th>
            <th class="border-top-right tr-size thcolor">Importador</th>
            <th class="border-top-right tr-size thcolor">Ordenante</th>
            <th class="border-top-right tr-size thcolor">Trader</th>
            <th class="border-top-right tr-size thcolor">País</th>
            <th class="border-top-right tr-size thcolor">Moeda</th>
            <th class="border-top-right tr-size thcolor">Vl.Invoice</th>
            <th class="border-top-right tr-size thcolor">Vl.C/ Desc.</th>
            <th class="border-top-right tr-size thcolor">Comissão</th>
            <th class="border-top-right tr-size thcolor">Data Cambio</th>
            <th class="border-top-right tr-size thcolor">Invoice</th>
            <th class="border-top-right tr-size thcolor">Pgto</th>
            <th class="border-top-right tr-size thcolor">Fech</th>
        </tr>

        @php
            $totValInvoice = 0;
            $totValComDesc = 0;
            $totComissao = 0;
        @endphp

        @foreach ($seq->registros as $row)
        @php
            $comissao = ($row->forma_comis === 'G') ? ($row->comissao ?? 0) : 0;
            $totValInvoice += $row->val_invoice ?? 0;
            $totValComDesc += $row->val_com_desc ?? 0;
            $totComissao += $comissao;

            $datCred = !empty($row->dat_cred) ? \Carbon\Carbon::parse($row->dat_cred)->format('d/m/Y') : '';
            $datNf = !empty($row->dat_nf) ? \Carbon\Carbon::parse($row->dat_nf)->format('d/m/Y') : '';
            $datCambioRow = !empty($row->dat_cambio) ? \Carbon\Carbon::parse($row->dat_cambio)->format('d/m/Y') : '';
        @endphp
        <tr>
            <td class="tr-size border-top-lr" align="center">{{ $row->num_ope }}</td>
            <td class="tr-size border-top-right" align="center">{{ $datCred }}</td>
            <td class="tr-size border-top-right" align="center">{{ $row->nota_fiscal }}</td>
            <td class="tr-size border-top-right" align="center">{{ $datNf }}</td>
            <td class="tr-size border-top-right" align="center">{{ $row->due }}</td>
            <td class="tr-size border-top-right" align="center">{{ $row->chave_due }}</td>
            <td class="tr-size border-top-right" align="left">{{ $row->importador }}</td>
            <td class="tr-size border-top-right" align="left">{{ $row->ordenante }}</td>
            <td class="tr-size border-top-right" align="left">{{ $row->trader }}</td>
            <td class="tr-size border-top-right" align="left">{{ $row->pais }}</td>
            <td class="tr-size border-top-right" align="center">{{ $row->moeda }}</td>
            <th class="tr-size border-top-right" align="right">{{ number_format($row->val_invoice ?? 0, 2, ',', '.') }}</th>
            <td class="tr-size border-top-right" align="right">{{ number_format($row->val_com_desc ?? 0, 2, ',', '.') }}</td>
            <th class="tr-size border-top-right" align="right">{{ $row->moeda }} {{ number_format($comissao, 2, ',', '.') }}</th>
            <td class="tr-size border-top-right" align="center">{{ $datCambioRow }}</td>
            <td class="tr-size border-top-right" align="center">{{ $row->invoice }}</td>
            <td class="tr-size border-top-right" align="center">{{ $row->cod_forma_pgto }}</td>
            <td class="tr-size border-top-right" align="center">{{ $row->fech }}</td>
        </tr>
        @endforeach

        {{-- Totais --}}
        <tr>
            <td colspan="11" class="border-top-right wrap-cell" style="font-size: 7px;">*Obs.: Informa se o valor <u>da ordem</u> é diferente a um pago T (Total), P (Parcial) ou A (Antecipado).</td>
            <th class="tr-size" align="right" style="border: 1px solid #000;">{{ number_format($totValInvoice, 2, ',', '.') }}</th>
            <th class="tr-size" align="right" style="border: 1px solid #000;">{{ number_format($totValComDesc, 2, ',', '.') }}</th>
            <th class="tr-size" align="right" style="border: 1px solid #000;">{{ $p->moeda ?? '' }} {{ number_format($totComissao, 2, ',', '.') }}</th>
            <td class="tr-size" style="border-top: 1px solid #000;" colspan="4"></td>
        </tr>
    </table>

    {{-- Cotação e total --}}
    <table width="100%" cellspacing="0" cellpadding="1">
        <tr height="3"><td></td></tr>
        <tr>
            <td colspan="11" align="right">Sem mais para o momento, antecipamos nossos agradecimentos.</td>
            <th></th>
            <th align="right">R$ {{ number_format($seq->val_cotacao, 4, ',', '.') }}</th>
            <th colspan="5"></th>
        </tr>
        <tr height="5"><td></td></tr>
        <tr>
            <th colspan="12" align="center"></th>
            <th colspan="2" align="center">{{ number_format($seq->total_taxa, 2, ',', '.') }}</th>
            <th colspan="4"></th>
        </tr>
        <tr>
            <th colspan="11" align="center"></th>
            <th class="borda-divisao" colspan="6" align="center"></th>
        </tr>
        <tr>
            <th colspan="11" align="center"></th>
            @if ($seq->tipo_calc === 'Líquido')
                <td colspan="6" align="center">Jaguafrangos Ind. Com. Alim. Ltda.</td>
            @else
                <th colspan="6" align="center">{{ $p->den_empresa ?? '' }}</th>
            @endif
        </tr>
    </table>

    {{-- Seção beneficiário (apenas para tipo_calc Líquido) --}}
    @if ($seq->tipo_calc === 'Líquido')
    <table width="100%" cellspacing="0" cellpadding="1" class="no-page-break">
        <tr height="3"><td></td></tr>
        <tr>
            <th width="30%" class="tr-size border-all thcolor" align="left">Beneficiaries...... OMARACO LTD</th>
            <td width="5%" class="tr-size"></td>
            <th width="35%" class="tr-size border-all thcolor" align="left">INFORMAÇÕES BANCÁRIAS DESEMBOLSO / TED</th>
            <td width="30%" class="tr-size"></td>
        </tr>
        <tr>
            <td class="tr-size border-lr" align="left"><b>Account nr....................</b> 38226563</td>
            <td class="tr-size"></td>
            <th class="tr-size border-lr" align="left">JAGUAFRANGOS IND E COM DE ALIMENTOS LTDA</th>
            <td class="tr-size"></td>
        </tr>
        <tr>
            <td class="tr-size border-lr" align="left"><b>Sort code.......................</b> 309897</td>
            <td class="tr-size"></td>
            <td class="tr-size border-lr" align="left">CNPJ: 85.090.033/0001-22</td>
            <td class="tr-size"></td>
        </tr>
        <tr>
            <td class="tr-size border-lr" align="left">3 Hollythorpe Road</td>
            <td class="tr-size"></td>
            <td class="tr-size border-lr" align="left"></td>
            <td class="tr-size"></td>
        </tr>
        <tr>
            <td class="tr-size border-lr" align="left">Sheffield S8 9NE United Kingdom</td>
            <td class="tr-size"></td>
            <th class="tr-size border-lr" align="left">{{ $p->nom_portador ?? '' }}</th>
            <td class="tr-size"></td>
        </tr>
        <tr>
            <th class="tr-size border-lr" align="left">07982 727505</th>
            <td class="tr-size"></td>
            <td class="tr-size border-lr" align="left">AGÊNCIA: {{ $p->agencia ?? '' }}</td>
            <td class="tr-size"></td>
        </tr>
        <tr>
            <th class="tr-size border-lr" align="left">Lloyds Bank - 1 high street</th>
            <td class="tr-size"></td>
            <td class="tr-size border-lr" align="left">CONTA: {{ $p->conta ?? '' }}</td>
            <td class="tr-size"></td>
        </tr>
        <tr>
            <td class="tr-size border-lr" align="left">Sheffield S1 2GA United Kingdom</td>
            <td class="tr-size"></td>
            <th class="tr-size border-lr" align="left"></th>
            <td class="tr-size"></td>
        </tr>
        <tr>
            <td class="tr-size border-lr" align="left">SWIFT: LOYDGB2L</td>
            <td class="tr-size"></td>
            <td class="tr-size border-lr" align="left"><b>Valor em USD.....:</b> {{ number_format($seq->total_us, 2, ',', '.') }}</td>
            <td class="tr-size"></td>
        </tr>
        <tr>
            <td class="tr-size border-lr" align="left">IBAN: GB67LOYD30989738226563</td>
            <td class="tr-size"></td>
            <td class="tr-size border-lr" align="left"><b>Taxa câmbio......:</b> R$ {{ number_format($seq->val_cotacao, 4, ',', '.') }}</td>
            <td class="tr-size"></td>
        </tr>
        <tr>
            <th class="tr-size border-all thcolor" align="left">Valor COMAG.....: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; USD {{ number_format($seq->tot_comissao, 2, ',', '.') }}</th>
            <td class="tr-size"></td>
            <th class="tr-size border-all thcolor" align="left">Valor em BRL.....: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; R$ {{ number_format($seq->total_taxa, 2, ',', '.') }}</th>
            <td class="tr-size"></td>
        </tr>
    </table>
    @endif

</div>
@endforeach
@endsection

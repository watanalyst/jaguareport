@extends('reports.pdf.layout')

@section('title', 'Comissão')

@section('content')
@php $linha0 = $dados[0] ?? null; @endphp

<header>
    <table width="100%">
        <tr>
            <td style="width: 20%; text-align: left;">
                <img src="{{ public_path('img/logo_jagua.png') }}" style="height: 40px;">
            </td>
            <td style="width: 60%; text-align: center;">
                <h2>COMISSÃO - RELATÓRIO PARA SIMPLES CONFERÊNCIA</h2>
            </td>
            <td style="width: 20%; text-align: right; font-size: 10px;">
                <div style="text-align: right;">
                    <span class="page-number"></span><br>
                    {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
                </div>
            </td>
        </tr>
        <tr>
            <td class="borda-divisao-dois" colspan="3"></td>
        </tr>
    </table>

    <table width="100%">
        <tr>
            <td colspan="3">
                <h3>{{ htmlentities($linha0->ep ?? '') }} {{ htmlentities($linha0->emp_reduz ?? '') }}</h3>
            </td>
        </tr>
        <tr>
            <td style="text-align: left;">
                <h3>
                    <span style="font-weight: normal;">Representante / Vendedor:</span>
                    {{ $linha0->cod_repres ?? '' }} {{ $linha0->nome_repres ?? '' }}
                </h3>
            </td>
            <td></td>
            <td style="text-align: right;">
                <strong>Período:</strong>
                {{ !empty($filtros['data_ini']) ? \Carbon\Carbon::parse($filtros['data_ini'])->format('d/m/Y') : '' }}
                a
                {{ !empty($filtros['data_fim']) ? \Carbon\Carbon::parse($filtros['data_fim'])->format('d/m/Y') : '' }}
            </td>
        </tr>
        <tr height="30px">
            <th align="left"></th>
        </tr>
    </table>
</header>

<footer>
    <div class="borda-footer" align="center">DEMONSTRATIVO PARA SIMPLES CONFERÊNCIA</div>
</footer>

<main>
    <table class="font-size-nove" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <td align="center">TÍTULO</td>
                <td align="center">NOTA</td>
                <td align="center">VDJ</td>
                <td align="left">CLIENTE</td>
                <td align="center">UF</td>
                <td align="center">EMISSÃO</td>
                <td align="center">VENCTO</td>
                <td align="center">CRÉDITO</td>
                <td align="center">PTD</td>
                <td align="center">FP</td>
                <td align="center">FR</td>
                <td align="center">PESO NF</td>
                <td align="center">VALOR</td>
                <td align="center">DESC</td>
                <td align="center">ABAT</td>
                <td align="center">VLR PAGO</td>
                <td align="center">FRETE</td>
                <td align="center">IMPOSTO</td>
                <td align="center">LIQUIDO</td>
                <td align="center">%CM</td>
                <td align="center">COMISSÃO</td>
            </tr>
            <tr>
                <td class="borda-divisao" colspan="21"></td>
            </tr>
        </thead>
        <tbody>
            @php
                $quantidade_titulos = 0;
                $total_valor    = 0;
                $total_desc     = 0;
                $total_abat     = 0;
                $total_pago     = 0;
                $total_frete    = 0;
                $total_imposto  = 0;
                $total_liquido  = 0;
                $total_comissao = 0;
            @endphp

            @foreach ($dados as $linha)
                <tr>
                    <td align="center">{{ $linha->titulo ?? '' }}</td>
                    <td align="center">{{ $linha->nota ?? '' }}</td>
                    <td align="center">{{ $linha->vdj ?? '' }}</td>
                    <td align="left">{{ $linha->cod_cliente ?? '' }} - <font class="font-size-nove">{{ $linha->nome_cliente ?? '' }}</font></td>
                    <td align="center">{{ $linha->uf ?? '' }}</td>
                    <td align="center">{{ !empty($linha->dt_emissao) ? \Carbon\Carbon::parse($linha->dt_emissao)->format('d/m/y') : '' }}</td>
                    <td align="center">{{ !empty($linha->dt_vencto) ? \Carbon\Carbon::parse($linha->dt_vencto)->format('d/m/y') : '' }}</td>
                    <td align="center">{{ !empty($linha->dt_credito) ? \Carbon\Carbon::parse($linha->dt_credito)->format('d/m/y') : '' }}</td>
                    <td align="center">{{ $linha->ptd ?? '' }}</td>
                    <td align="center">{{ $linha->fp ?? '' }}</td>
                    <td align="center">{{ $linha->fr ?? '' }}</td>
                    <td align="right">{{ number_format($linha->peso_nf ?? 0, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($linha->valor ?? 0, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($linha->valor_desc ?? 0, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($linha->val_abat ?? 0, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($linha->val_pago ?? 0, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($linha->frete ?? 0, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($linha->val_imposto ?? 0, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($linha->val_liquido ?? 0, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($linha->pct_comis ?? 0, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($linha->comissao ?? 0, 2, ',', '.') }}</td>
                </tr>
                @php
                    $quantidade_titulos++;
                    $total_valor    += $linha->valor ?? 0;
                    $total_desc     += $linha->valor_desc ?? 0;
                    $total_abat     += $linha->val_abat ?? 0;
                    $total_frete    += $linha->frete ?? 0;
                    $total_imposto  += $linha->val_imposto ?? 0;
                    $total_liquido  += $linha->val_liquido ?? 0;
                    $total_comissao += $linha->comissao ?? 0;
                    $total_pago     += $linha->recebidos ?? 0;
                @endphp
            @endforeach

            @php
                $total_recebido = $total_pago - $total_desc - $total_abat;
                $total_fob      = $total_pago - $total_desc - $total_abat - $total_frete - $total_imposto;
            @endphp

            <tr>
                <td class="borda-divisao" colspan="21"></td>
            </tr>
            <tr>
                <th colspan="7" align="left">{{ $quantidade_titulos }} <span style="font-weight: normal;">Título(s)</span></th>
                <td colspan="5" align="left">Total Valor dos Títulos:</td>
                <th colspan="1" align="right">{{ number_format($total_pago, 2, ',', '.') }}</th>
                <td colspan="1"></td>
                <td colspan="2" align="left">Total Frete:</td>
                <th colspan="1" align="right">{{ number_format($total_frete, 2, ',', '.') }}</th>
                <td colspan="4"></td>
            </tr>
            <tr>
                <td colspan="7"><strong>Obs.:</strong> O valor da comissão apresentado neste relatório está sujeito à retenção de impostos</td>
                <td colspan="5" align="left">Total Desconto:</td>
                <th colspan="1" align="right">{{ number_format($total_desc, 2, ',', '.') }}</th>
                <td colspan="1"></td>
                <td colspan="2" align="left">Total Imposto:</td>
                <th colspan="1" align="right">{{ number_format($total_imposto, 2, ',', '.') }}</th>
                <td colspan="4"></td>
            </tr>
            <tr>
                <td colspan="7">na fonte, conforme a legislação vigente, especialmente o IRRF, à alíquota de 1,5%.</td>
                <td colspan="5" align="left">Total Abatimento:</td>
                <th colspan="1" align="right">{{ number_format($total_abat, 2, ',', '.') }}</th>
                <td colspan="1"></td>
                <td colspan="2" align="left">Valor FOB:</td>
                <th colspan="1" align="right">{{ number_format($total_fob, 2, ',', '.') }}</th>
                <td colspan="4"></td>
            </tr>
            <tr>
                <td colspan="7"></td>
                <th colspan="5" align="left">Total Recebido:</th>
                <th colspan="1" align="right">{{ number_format($total_recebido, 2, ',', '.') }}</th>
                <td colspan="1"></td>
                <th colspan="2" align="left" class="comissao-valor">Comissão (R$):</th>
                <th colspan="1" align="right" class="comissao-valor">{{ number_format($total_comissao, 2, ',', '.') }}</th>
                <td colspan="4"></td>
            </tr>
            <tr>
                <td class="borda-divisao" colspan="21"></td>
            </tr>
        </tbody>
    </table>
</main>
@endsection

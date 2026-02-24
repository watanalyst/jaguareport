@php
    $quantidade_titulos = 0;
    $total_valor = $total_desc = $total_abat = $total_pago = $total_frete = 0;
    $total_imposto = $total_liquido = $total_comissao = 0;
@endphp

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
        @foreach ($dados as $linha)
            @php
                $quantidade_titulos++;
                $total_valor    += $linha->valor ?? 0;
                $total_desc     += $linha->valor_desc ?? 0;
                $total_abat     += $linha->val_abat ?? 0;
                $total_pago     += $linha->val_pago ?? 0;
                $total_frete    += $linha->frete ?? 0;
                $total_imposto  += $linha->val_imposto ?? 0;
                $total_liquido  += $linha->val_liquido ?? 0;
                $total_comissao += $linha->comissao ?? 0;
            @endphp

            <tr>
                <td align="center">{{ $linha->titulo }}</td>
                <td align="center">{{ $linha->nota }}</td>
                <td align="center">{{ $linha->vdj }}</td>
                <td align="left">{{ $linha->cliente }} -
                    <span class="font-size-nove">{{ $linha->nom_cliente }}</span>
                </td>
                <td align="center">{{ $linha->uf }}</td>
                <td align="center">{{ optional($linha->dat_emis)->format('d/m/y') }}</td>
                <td align="center">{{ optional($linha->dat_vencto_s_desc)->format('d/m/y') }}</td>
                <td align="center">{{ optional($linha->dat_credito)->format('d/m/y') }}</td>
                <td align="center">{{ $linha->ptd }}</td>
                <td align="center">{{ $linha->fp }}</td>
                <td align="center">{{ $linha->fr }}</td>
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
        @endforeach

        @php $total_recebido = $total_valor - $total_desc - $total_abat; @endphp

        <tr><td class="borda-divisao" colspan="21"></td></tr>

        <tr>
            <th colspan="7" align="left">{{ $quantidade_titulos }} <span style="font-weight: normal;">Título(s)</span></th>
            <td colspan="5" align="left">Total Valor dos Títulos:</td>
            <th align="right">{{ number_format($total_valor, 2, ',', '.') }}</th>
            <td></td>
            <td colspan="2" align="left">Total Frete:</td>
            <th align="right">{{ number_format($total_frete, 2, ',', '.') }}</th>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="7"><strong>Obs.:</strong>O valor da comissão apresentado neste relatório está sujeito à retenção de impostos</td>
            <td colspan="5" align="left">Total Desconto:</td>
            <th align="right">{{ number_format($total_desc, 2, ',', '.') }}</th>
            <td></td>
            <td colspan="2" align="left">Total Imposto:</td>
            <th align="right">{{ number_format($total_imposto, 2, ',', '.') }}</th>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="7">na fonte, conforme a legislação vigente, especialmente o IRRF, à alíquota de 1,5%.</td>
            <td colspan="5" align="left">Total Abatimento:</td>
            <th align="right">{{ number_format($total_abat, 2, ',', '.') }}</th>
            <td></td>
            <td colspan="2" align="left">Valor FOB:</td>
            <th align="right">{{ number_format($total_liquido, 2, ',', '.') }}</th>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="7"></td>
            <th colspan="5" align="left">Total Recebido:</th>
            <th align="right">{{ number_format($total_recebido, 2, ',', '.') }}</th>
            <td></td>
            <th colspan="2" align="left" class="comissao-valor">Comissão (R$):</th>
            <th align="right" class="comissao-valor">{{ number_format($total_comissao, 2, ',', '.') }}</th>
            <td colspan="4"></td>
        </tr>
        <tr><td class="borda-divisao" colspan="21"></td></tr>
    </tbody>
</table>

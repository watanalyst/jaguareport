@php
    $bg   = $branding['bg_color'];
    $fc   = $branding['font_color'];
    $logo = public_path($branding['logo']);

    $h = $header;

    // Somas agregadas de todos os itens
    $sumQtdBrt   = $items->sum('rateio_palete');
    $sumEmbalag  = $items->sum('tot_embalag');
    $sumValFob   = $items->sum('val_fob');
    $sumValTotal = $items->sum('val_tot_item');

    // Produto: se múltiplos itens, mostra "MISTO"
    $prodItem = (($h->qtd_itens ?? 1) > 1)
        ? 'MISTO - VIDE PROF.'
        : trim($h->cod_item ?? '');

    // Checkbox helper: quadrado fixo — vazio ou preenchido (azul padrão + ✓ branco)
    $ck = fn($v) => (trim($v ?? '') === 'CHECKED')
        ? '<span style="display: inline-block; width: 11px; height: 11px; border: 1px solid #2860a1; background: #2860a1; color: #fff; font-size: 10px; font-weight: bold; text-align: center; line-height: 12px;">&#10004;</span>'
        : '<span style="display: inline-block; width: 11px; height: 11px; border: 1px solid #555;"></span>';

    // Texto condicional para confirmação de pagamento
    $textConfPgto = trim($h->conf_pgto ?? '')
        ? 'Confirmação PGTO - '
        : 'Confirmação PGTO';

    $dataImp = now()->format('d/m/Y H:i:s');
    $usuario = auth()->user()->name ?? 'Sistema';
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Check List {{ trim($h->processo ?? '') }}</title>
    <style>
        @page { margin: 20px 20px 15px 20px; }

        * { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; }

        body { margin: 0; padding: 0; color: #333; font-size: 0; line-height: 0; }

        table { border-collapse: separate; border-spacing: 0; width: 100%; margin: 0; padding: 0; font-size: 10px; line-height: normal; }

        th, td { vertical-align: top; padding: 1px 3px; font-size: 10px; }

        .b-all { border: 1px solid #000; }
        .b-t   { border-top: 1px solid #000; }
        .b-b   { border-bottom: 1px solid #000; }
        .b-l   { border-left: 1px solid #000; }
        .b-r   { border-right: 1px solid #000; }

        .proc-title { font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
{{-- TABLE 1: HEADER --}}
<table cellspacing="0" cellpadding="2">
    <tr>
        <td width="25%" rowspan="3" align="center" style="vertical-align: middle;">
            @if(file_exists($logo))
                <img src="{{ $logo }}" alt="LOGO" width="100">
            @endif
        </td>
        <td width="50%" align="center" class="b-all">Número Plano: {{ trim($h->num_plano ?? '') }} / Refer. Sig SIF: {{ trim($h->refer_sig_sif ?? '') }}</td>
        <td width="3%"></td>
        <td width="22%" class="b-t b-l b-r" style="padding-left: 4px;">( {{ trim($h->rodoviario ?? '') }} ) &nbsp;<b>RODOVIÁRIO</b></td>
    </tr>
    <tr>
        <td align="center" class="b-b b-l b-r">1 Termógrafo JAGUA (Amarelo)</td>
        <td></td>
        <td class="b-l b-r" style="padding-left: 4px;">( {{ trim($h->multimodal ?? '') }} ) &nbsp;<b>MULTIMODAL</b></td>
    </tr>
    <tr>
        <td align="center" class="b-b b-l b-r">+ FOTO; Temperatura <b>-{{ trim($h->cont_temperatura ?? '') }}ºC {{ trim($h->ies_paletizado ?? '') }}</b></td>
        <td></td>
        <td class="b-b b-l b-r" style="padding-left: 4px;"><b>CÓDIGO ISO: {{ trim($h->cod_pais ?? '') }}</b></td>
    </tr>
    <tr>
        <td height="05px"></td>
    </tr>
    <tr>
        <td></td>
        <td align="center" style="padding: 1px 0;">
            <span class="proc-title">{{ trim($h->processo ?? '') }}</span>
        </td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td height="05px"></td>
    </tr>
</table>{{-- TABLE 2: INFORMAÇÕES DO PROCESSO --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <td width="39%" class="b-t b-l">Purchase Ordem: {{ trim($h->ordem ?? '') }}</td>
        <td width="31%" class="b-t b-l"><b>Cliente: {{ trim($h->nom_reduzido ?? '') }}</b></td>
        <td width="30%" class="b-t b-l b-r">Produto: {{ trim($h->item_reduz ?? '') }} {{ $prodItem }}</td>
    </tr>
    <tr>
        <td class="b-t b-l">ETD origem: {{ trim($h->dat_etd ?? '') }} &nbsp; ETA destino: {{ trim($h->dat_eta ?? '') }}</td>
        <td class="b-t b-l">Consignatário: {{ trim($h->cod_consignat ?? '') }} {{ trim($h->texto1_consignat ?? '') }}</td>
        <td class="b-t b-l b-r">Peso Líquido: {{ number_format($h->qtd ?? 0, 3, ',', '.') }}</td>
    </tr>
    <tr>
        <td class="b-t b-l">CNTR: {{ trim($h->cod_container ?? '') }}</td>
        <td class="b-t b-l">Seal: {{ trim($h->cod_lacre ?? '') }}</td>
        <td class="b-t b-l b-r">Peso Bruto: {{ number_format($sumQtdBrt, 3, ',', '.') }}</td>
    </tr>
    <tr>
        <td class="b-t b-l">Porto Embarque: <b>{{ trim($h->local_embarque ?? '') }}</b></td>
        <td class="b-t b-l">Seal SIF: {{ trim($h->cod_lacre_sif ?? '') }}</td>
        <td class="b-t b-l b-r">Tara: {{ number_format($h->tara_cont ?? 0, 2, ',', '.') }} &nbsp;&nbsp;/&nbsp;&nbsp; Caixas: {{ number_format($sumEmbalag, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td class="b-t b-l">Porto Destino: {{ trim($h->porto_destino ?? '') }}</td>
        <td class="b-t b-l">DUE: {{ trim($h->cod_due ?? '') }}</td>
        <td class="b-t b-l b-r">Comissão: {{ trim($h->den_moeda ?? '') }} {{ number_format($h->val_comis ?? 0, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <td class="b-t b-l">País Destino: {{ trim($h->pais_destino ?? '') }}</td>
        <td class="b-t b-l">Valor CFR: {{ trim($h->den_moeda ?? '') }} {{ number_format($sumValTotal, 2, ',', '.') }}</td>
        <td class="b-t b-l b-r">Valor Fob: {{ trim($h->den_moeda ?? '') }} {{ number_format($sumValFob, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <td class="b-t b-l">Despachante: <b>{{ trim($h->despachante ?? '') }}</b> &nbsp;&nbsp; Terminal: <b>{{ trim($h->terminal ?? '') }}</b></td>
        <td class="b-t b-l">Transportadora: {{ trim($h->tranp_reduzida ?? '') }}</td>
        <td class="b-t b-l b-r"><b>PLACA:</b> {{ trim($h->placa ?? '') }}</td>
    </tr>
    <tr>
        <td class="b-t b-l b-b">Preço: <b>{{ trim($h->den_moeda ?? '') }} {{ number_format($h->preco_unit ?? 0, 3, ',', '.') }}</b></td>
        <td class="b-t b-l b-b">Navio (01): {{ trim($h->den_navio_aviao ?? '') }}</td>
        <td class="b-t b-l b-r b-b">NCM: {{ trim($h->ncm ?? '') }}</td>
    </tr>
    <tr>
        <td height="10px" colspan="99"></td>
    </tr>
</table>{{-- TABLE 3: MOTIVO NAVIO + PEDIDO LOGIX + VGM --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <td class="b-t b-l b-r" colspan="5"><b>Motivo da alteração do navio:</b></td>
    </tr>
    <tr>
        <th align="left" class="b-l"><u>PEDIDO LOGIX: {{ trim($h->num_pedido ?? '') }}</u></th>
        <th align="left"><u>LANÇADO</u></th>
        <td align="left">NO EDATA DIA: {{ trim($h->dat_lanc_edata ?? '') }}</td>
        <td align="left">Nº DA CARGA: {{ trim($h->carga_edata ?? '') }}</td>
        <td align="left" class="b-r">NÚMERO HALAL: {{ trim($h->num_halal ?? '') }}</td>
    </tr>
    <tr>
        <th align="left" class="b-b b-l" colspan="3">( {{ trim($h->ies_env_prog ?? '') }} ) ENVIO DE PROGRAMAÇÃO AO CLIENTE</th>
        <td align="left" class="b-b b-r" colspan="2">VGM: {{ number_format($h->vgm ?? 0, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <td height="10px" colspan="99"></td>
    </tr>
</table>{{-- TABLE 4: CONDIÇÃO PAGAMENTO / INCOTERM --}}
<table cellspacing="0" cellpadding="3">
    <tr>
        <th colspan="4" align="left" class="b-t b-l b-r">{{ trim($h->cond_pgto_modal ?? '') }} - {{ trim($h->cod_incoterm ?? '') }}</th>
    </tr>
    <tr>
        <th width="15%" align="left" class="b-b b-l">AC: {{ trim($h->num_ac ?? '') }}</th>
        <th width="41%" class="b-b">OP: {{ trim($h->op ?? '') }}</th>
        <th width="22%" class="b-b">Dead lines: Draft {{ trim($h->dat_deadline_draft ?? '') }}</th>
        <th width="22%" class="b-b b-r">Liberação: {{ trim($h->dat_liberacao ?? '') }}</th>
    </tr>
    <tr>
        <td height="10px" colspan="99"></td>
    </tr>
</table>{{-- TABLE 5: SEÇÃO 1 — BOOKING / PAGAMENTO / OBS --}}
<table cellspacing="0" cellpadding="1">
    <tr>
        <td class="b-t b-l" width="4%" align="center">1</td>
        <td class="b-t b-l" width="6%" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-t b-l" width="33%">&nbsp; ( {{ trim($h->pgto_antec ?? '') }} ) Recebimento de pgto antecipado {{ trim($h->pct_adiant ?? '') }} %</td>
        <td class="b-t" width="19%">( {{ trim($h->conf_pgto ?? '') }} ) {{ $textConfPgto }}</td>
        <td class="b-t" width="19%"></td>
        <td class="b-t b-r" width="19%"></td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-l"><b>&nbsp; ( {{ trim($h->ies_booking ?? '') }} ) Booking Nr: {{ trim($h->cod_booking ?? '') }}</b></td>
        <td><b>Retirada: {{ trim($h->dat_retirada ?? '') }}</b></td>
        <td><b>Entrada Jagua: {{ trim($h->dat_entrada ?? '') }}</b></td>
        <td class="b-r">Saída Jagua: {{ trim($h->dat_saida ?? '') }}</td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-b b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-l b-r" colspan="4">&nbsp; Obs: {{ trim($h->campo_obs1 ?? '') }}</td>
    </tr>
    <tr>
        <td class="b-b b-l"></td>
        <td class="b-b b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-b b-l b-r" colspan="4">&nbsp; Import Permit: {{ trim($h->import_permit ?? '') }}</td>
    </tr>
    <tr>
        <td height="10px" colspan="99"></td>
    </tr>
</table>{{-- TABLE 6: SEÇÃO 2 — DESPACHO (D / S / C) --}}
<table cellspacing="0" cellpadding="1">
    <tr>
        <td class="b-t b-l" width="4%" align="center">2</td>
        <td class="b-t b-l" width="6%" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-t b-l" width="4%">&nbsp;{!! $ck($h->ies_csi_dsc_ckb ?? '') !!}</td>
        <td class="b-t" width="40%">CSI {{ trim($h->ies_csi_dsc ?? '') }} &nbsp;&nbsp; NF: {{ trim($h->nota_fiscal ?? '') }}</td>
        <td class="b-t b-r" width="46%">Data da NF: {{ trim($h->data_emissao_nf ?? '') }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Data Carregamento: {{ trim($h->dat_carreg ?? '') }}</td>
    </tr>
    <tr>
        <td class="b-l" align="center"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-l">&nbsp;{!! $ck($h->ies_lista_carga_ckb ?? '') !!}</td>
        <td>( {{ trim($h->ies_lista_carga ?? '') }} ) Lista de Carga p/Despachante {{ trim($h->dat_lista_carga ?? '') }}</td>
        <td class="b-r">( {{ trim($h->inst_compl_desp ?? '') }} ) Instrução COMPLETA para Despachante</td>
    </tr>
    <tr>
        <td class="b-l" align="center">D</td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-l"></td>
        <td></td>
        <td class="b-r"></td>
    </tr>
    <tr>
        <td class="b-l" align="center">S</td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-l">&nbsp;{!! $ck($h->ies_due_ckb ?? '') !!}</td>
        <td><b><u>Receber:</u> ( {{ trim($h->ies_due ?? '') }} ) DUE {{ trim($h->dat_due ?? '') }} / ( {{ trim($h->ies_draft ?? '') }} ) Draft {{ trim($h->dat_draft ?? '') }}</b></td>
        <td class="b-r"><b><u>Aprovação:</u> ( {{ trim($h->ies_aprov_due ?? '') }} ) DUE {{ trim($h->dat_aprov_due ?? '') }} / ( &nbsp; ) Draft_________</b></td>
    </tr>
    <tr>
        <td class="b-l" align="center">C</td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-l"></td>
        <td></td>
        <td class="b-r"></td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-l">&nbsp;{!! $ck($h->ies_seared_ckb ?? '') !!}</td>
        <td>( {{ trim($h->ies_seared ?? '') }} ) Enviar docts para legalizar à <b>SEARED {{ trim($h->dat_seared ?? '') }}</b></td>
        <td class="b-r">( {{ trim($h->ies_prev_ret ?? '') }} ) Previsão de retorno {{ trim($h->dat_prev_ret ?? '') }}</td>
    </tr>
    <tr>
        <td class="b-b b-l"></td>
        <td class="b-b b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-b b-l">&nbsp;{!! $ck($h->ies_ippex_ckb ?? '') !!}</td>
        <td class="b-b">( {{ trim($h->ies_ippex ?? '') }} ) CO enviado para Ippex {{ trim($h->dat_ippex ?? '') }}</td>
        <td class="b-b b-r"></td>
    </tr>
    <tr>
        <td height="10px" colspan="99"></td>
    </tr>
</table>{{-- TABLE 7: SEÇÃO 3 — EMBARQUE (E-m-b-a-r-q-u-e) --}}
<table cellspacing="0" cellpadding="1">
    <tr>
        <td class="b-t b-l" width="4%" align="center">3</td>
        <td class="b-t b-l" width="6%" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"><b>E</b></td>
        <td class="b-t b-l" width="4%"></td>
        <td class="b-t" width="30%" align="left"><b>Documentos enviados ao cliente:</b></td>
        <td class="b-t" width="26%"><b>OK Cliente:</b></td>
        <td class="b-t b-r" width="30%">** Tem CC: ( {{ trim($h->ies_cc_s ?? '') }} ) SIM NÃO ( {{ trim($h->ies_cc_n ?? '') }} ) {{ trim($h->dat_cc ?? '') }}</td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"><b>m</b></td>
        <td class="b-l">&nbsp;{!! $ck($h->ies_bl ?? '') !!}</td>
        <td>BL {{ trim($h->dat_bl ?? '') }}</td>
        <td>( {{ trim($h->ies_bl_ok ?? '') }} ) {{ trim($h->dat_bl_ok ?? '') }}</td>
        <td class="b-r">( {{ trim($h->ies_csi_sigsif ?? '') }} ) CSI Lançado no SigSif</td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"><b>b</b></td>
        <td class="b-l">&nbsp;{!! $ck($h->ies_draft_bl ?? '') !!}</td>
        <td>Draft/BL {{ trim($h->dat_draft_bl ?? '') }}</td>
        <td>( {{ trim($h->ies_draft_bl_ok ?? '') }} ) {{ trim($h->dat_draft_bl_ok ?? '') }}</td>
        <td class="b-r"></td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"><b>a</b></td>
        <td class="b-l">&nbsp;{!! $ck($h->ies_fat_bl ?? '') !!}</td>
        <td>Fatura {{ trim($h->dat_fat_bl ?? '') }}</td>
        <td class="b-r" colspan="2">( {{ trim($h->ies_fat_bl_ok ?? '') }} ) {{ trim($h->dat_fat_bl_ok ?? '') }}</td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;">r</td>
        <td class="b-l">&nbsp;{!! $ck($h->ies_pack_list ?? '') !!}</td>
        <td>Packing List {{ trim($h->dat_pack_list ?? '') }}</td>
        <td>( {{ trim($h->ies_pack_list_ok ?? '') }} ) {{ trim($h->dat_pack_list_ok ?? '') }}</td>
        <td class="b-r"></td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;">q</td>
        <td class="b-l">&nbsp;{!! $ck($h->csi_cli ?? '') !!}</td>
        <td>CSI {{ trim($h->dat_csi_cli ?? '') }}</td>
        <td>( {{ trim($h->csi_cli_ok ?? '') }} ) {{ trim($h->dat_csi_cli_ok ?? '') }}</td>
        <td class="b-r"></td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;">u</td>
        <td class="b-l">&nbsp;{!! $ck($h->ies_co ?? '') !!}</td>
        <td>CO {{ trim($h->dat_co ?? '') }}</td>
        <td>( {{ trim($h->ies_co_ok ?? '') }} ) {{ trim($h->dat_co_ok ?? '') }}</td>
        <td class="b-r"></td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;">e</td>
        <td class="b-l">&nbsp;{!! $ck($h->check_halal ?? '') !!}</td>
        <td>{{ (trim($h->check_halal ?? '') === 'CHECKED') ? 'Com Halal' : 'Halal' }} {{ trim($h->dat_halal ?? '') }}</td>
        <td>( {{ trim($h->ies_halal_ok ?? '') }} ) {{ trim($h->dat_halal_ok ?? '') }}</td>
        <td class="b-r"></td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-l">&nbsp;{!! $ck($h->ies_outros ?? '') !!}</td>
        <td>Outros {{ trim($h->dat_outros ?? '') }}</td>
        <td>( {{ trim($h->ies_outros_ok ?? '') }} ) {{ trim($h->dat_outros_ok ?? '') }}</td>
        <td class="b-r">CNPJ: {{ trim($h->num_cgc ?? '') }}</td>
    </tr>
    <tr>
        <td class="b-b b-l"></td>
        <td class="b-b b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-b b-l"></td>
        <td class="b-b b-r" colspan="3"></td>
    </tr>
    <tr>
        <td height="10px" colspan="99"></td>
    </tr>
</table>{{-- TABLE 8: SEÇÃO 4 — DOCUMENTAÇÃO / PAGAMENTO --}}
<table cellspacing="0" cellpadding="1">
    <tr>
        <td class="b-t b-l" width="4%" align="center">4</td>
        <td class="b-t b-l" width="6%" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"><b>Do</b></td>
        <td class="b-t b-l" width="4%"></td>
        <td class="b-t" width="43%" align="left"><b>{{ trim($h->cond_pgto_modal ?? '') }}</b></td>
        <td class="b-t b-r" width="43%"></td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"><b>cu</b></td>
        <td class="b-l b-r" colspan="3"></td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"><b>men</b></td>
        <td class="b-l">&nbsp;{!! $ck($h->dhl_ckb ?? '') !!}</td>
        <td>Documentos originais para o banco DHL {{ trim($h->dhl ?? '') }}</td>
        <td class="b-r">Data: {{ trim($h->dat_dhl ?? '') }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Entrega: {{ trim($h->dat_ent_dhl ?? '') }}</td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"><b>ta</b></td>
        <td class="b-l"></td>
        <td class="b-r" colspan="2">Data da confirmação de pagamento: {{ trim($h->dat_confirm_pgto ?? '') }}</td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"><b>ção</b></td>
        <td class="b-b b-l b-r" colspan="3"></td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"><b>/</b></td>
        <td class="b-l"></td>
        <td class="b-r" colspan="2"></td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"><b>Pa</b></td>
        <td class="b-l">&nbsp;{!! $ck($h->dhl_sw_ckb ?? '') !!}</td>
        <td>Swift / Data {{ trim($h->dat_swift ?? '') }}</td>
        <td class="b-r">Data da confirmação de pagamento: {{ trim($h->dat_confirm_pgto_swift ?? '') }}</td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"><b>ga</b></td>
        <td class="b-l"></td>
        <td>Envio dos documentos originais ao cliente, DHL {{ trim($h->dhl_sw ?? '') }}</td>
        <td class="b-r">Data: {{ trim($h->dat_dhl_sw ?? '') }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Entregue: {{ trim($h->dat_ent_dhl_sw ?? '') }}</td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"><b>to</b></td>
        <td class="b-l b-r" colspan="3"></td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-l b-r" colspan="3"></td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <td class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-l b-r" colspan="3"></td>
    </tr>
    <tr>
        <td class="b-b b-l"></td>
        <td class="b-b b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-b b-l b-r" colspan="3"></td>
    </tr>
    <tr>
        <td height="10px" colspan="99"></td>
    </tr>
</table>{{-- TABLE 9: SEÇÃO 5 — PAGAMENTOS (Pa-ga-men-tos) --}}
<table cellspacing="0" cellpadding="1">
    <tr>
        <td class="b-t b-l" width="4%" align="center">5</td>
        <th class="b-t b-l" width="6%" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;">Pa</th>
        <td class="b-t b-l" width="30%"></td>
        <td class="b-t" width="30%"></td>
        <td class="b-t b-r" width="30%"></td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <th class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;">ga</th>
        <td class="b-l">&nbsp; FRETE Marítimo Valor: {{ trim($h->den_moeda ?? '') }} {{ number_format($h->val_frete_embarque ?? 0, 2, ',', '.') }}</td>
        <td>Agente / Armador: {{ trim($h->agente_armador ?? '') }}</td>
        <td class="b-r">Taxas Locais: {{ number_format($h->val_desp_nacional ?? 0, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <th class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;">men</th>
        <td class="b-l">&nbsp;{!! $ck($h->rodoviario_ckb ?? '') !!} FRETE RODOVIÁRIO Valor: {{ number_format($h->val_frete_rodo ?? 0, 2, ',', '.') }}</td>
        <td>Transportadora {{ trim($h->tranp_reduzida ?? '') }}</td>
        <td class="b-r"></td>
    </tr>
    <tr>
        <td class="b-l"></td>
        <th class="b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;">tos</th>
        <td class="b-l b-r" colspan="3"></td>
    </tr>
    <tr>
        <td class="b-b b-l"></td>
        <td class="b-b b-l" style="background: {{ $bg }}; color: {{ $fc }}; border-color: {{ $bg }}; text-align: center;"></td>
        <td class="b-b b-l b-r" colspan="3"></td>
    </tr>
</table>{{-- FOOTER --}}
<table cellspacing="0" cellpadding="2">
    <tr>
        <td align="center">Impresso por {{ $usuario }} às {{ $dataImp }}</td>
    </tr>
</table>
</body>
</html>

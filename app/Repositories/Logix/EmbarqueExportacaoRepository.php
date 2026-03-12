<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class EmbarqueExportacaoRepository extends BaseLogixRepository
{
    /**
     * Pesquisa embarques de exportação com filtros dinâmicos.
     */
    public function search(array $params): Collection
    {
        $where = ["COD_SITUACAO NOT IN('9', 'NI')"];
        $bindings = [];

        if (! empty($params['empresa'])) {
            $empresas = array_map('trim', explode(',', $params['empresa']));
            if (count($empresas) === 1) {
                $where[] = 'EMPRESA = :empresa';
                $bindings['empresa'] = $empresas[0];
            } else {
                $placeholders = [];
                foreach ($empresas as $i => $emp) {
                    $key = "empresa_{$i}";
                    $placeholders[] = ":{$key}";
                    $bindings[$key] = $emp;
                }
                $where[] = 'EMPRESA IN (' . implode(',', $placeholders) . ')';
            }
        }

        if (! empty($params['dt_prev_ini'])) {
            $where[] = "DATA_PREV >= TO_DATE(:dt_prev_ini, 'YYYY-MM-DD')";
            $bindings['dt_prev_ini'] = $params['dt_prev_ini'];
        }

        if (! empty($params['dt_prev_fim'])) {
            $where[] = "DATA_PREV <= TO_DATE(:dt_prev_fim, 'YYYY-MM-DD')";
            $bindings['dt_prev_fim'] = $params['dt_prev_fim'];
        }

        if (! empty($params['situacao_ped'])) {
            $where[] = 'TRIM(SITUACAO) = :situacao_ped';
            $bindings['situacao_ped'] = $params['situacao_ped'];
        }

        if (! empty($params['cod_item'])) {
            $where[] = 'TRIM(COD_ITEM) = :cod_item';
            $bindings['cod_item'] = trim($params['cod_item']);
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT
                TRIM(EMPRESA) AS empresa,
                TRIM(NOM_AGENTE) AS nom_agente,
                TRIM(BUYER) AS buyer,
                TRIM(NOM_CONSIG_RED) AS nom_consig_red,
                NUM_VDJ AS num_vdj,
                DATA_PREV AS prev_vdj,
                TRIM(PROCESSOS) AS processos,
                TRIM(PO_CLIENTE) AS po_cliente,
                TRIM(NUM_PEDIDO) AS num_pedido,
                QTD_TOTAL AS qtd_total,
                TRIM(COD_ITEM) AS cod_item,
                TRIM(ITEM_REDUZ) AS item_reduz,
                TRIM(PAIS_DESTINO) AS pais_destino,
                TRIM(LOCAL_DESTINO) AS porto_destino,
                TRIM(BOOKING) AS booking,
                TRIM(NOM_ARMADOR) AS nom_armador,
                TRIM(DEN_NAVIO_AVIAO) AS navio,
                DATA_ETD_DEPARTURE AS etd,
                DATA_ETA_ARRIVAL AS eta,
                TRIM(LOCAL_EMBARQUE) AS local_embarque,
                TRIM(DADOS_TRANSP) AS dados_transp,
                TRIM(SITUACAO) AS sit,
                TRIM(OBS_VDJ) AS obs_vdj,
                TRIM(IMPORT_PERMIT) AS import_permit,
                VAL_RECEB_ADTO AS val_receb_adto
            FROM LOGIXPRD.VW_BI_EXPORT
            WHERE {$whereClause}
            ORDER BY DATA_PREV DESC, NUM_VDJ DESC
        ";

        return $this->query($sql, $bindings)
            ->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    /**
     * Busca itens distintos para o autocomplete.
     */
    public function distinctItems(string $empresa): Collection
    {
        $sql = "
            SELECT DISTINCT
                TRIM(COD_ITEM) AS cod_item,
                TRIM(ITEM_REDUZ) AS item_reduz
            FROM LOGIXPRD.VW_BI_EXPORT
            WHERE COD_SITUACAO NOT IN('9', 'NI')
              AND EMPRESA = :empresa
            ORDER BY TRIM(COD_ITEM)
        ";

        return $this->query($sql, ['empresa' => $empresa])
            ->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }
}

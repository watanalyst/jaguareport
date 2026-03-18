<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class ComissaoExportacaoRepository extends BaseLogixRepository
{
    public function search(array $params): Collection
    {
        $where = ['1=1'];
        $bindings = [];

        if (! empty($params['nom_agente'])) {
            $where[] = 'TRIM(NOM_AGENTE) = :nom_agente';
            $bindings['nom_agente'] = trim($params['nom_agente']);
        }

        if (! empty($params['dat_confirm_ini'])) {
            $where[] = "DAT_CONFIRM_PGTO >= TO_DATE(:dat_confirm_ini, 'YYYY-MM-DD')";
            $bindings['dat_confirm_ini'] = $params['dat_confirm_ini'];
        }

        if (! empty($params['dat_confirm_fim'])) {
            $where[] = "DAT_CONFIRM_PGTO <= TO_DATE(:dat_confirm_fim, 'YYYY-MM-DD')";
            $bindings['dat_confirm_fim'] = $params['dat_confirm_fim'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT
                TRIM(INVOICE) AS invoice,
                TRIM(PO_CLIENTE) AS po_cliente,
                DAT_RECEB_ADTO,
                DAT_CONFIRM_PGTO,
                TRIM(NOM_AGENTE) AS nom_agente,
                VAL_COMIS AS val_pct_comis,
                SUM(VAL_TOT_ITEM) AS val_tot_item,
                VAL_FRETE AS freight,
                ((SUM(VAL_TOT_ITEM) - VAL_FRETE) * 0.01) AS tot_comis_item
            FROM LOGIXPRD.VW_BI_EXPORT
            WHERE {$whereClause}
            GROUP BY
                EMPRESA, NUM_PROCESSO, ANO_PROCESSO, EMBARQUE, INVOICE, PO_CLIENTE,
                DAT_RECEB_ADTO, DAT_CONFIRM_PGTO, NOM_AGENTE, VAL_COMIS, VAL_FRETE
            ORDER BY
                EMPRESA DESC, NUM_PROCESSO DESC, ANO_PROCESSO DESC, EMBARQUE DESC
        ";

        return $this->query($sql, $bindings)
            ->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    public function distinctAgentes(): Collection
    {
        $sql = "
            SELECT DISTINCT TRIM(NOM_AGENTE) AS nom_agente
            FROM LOGIXPRD.VW_BI_EXPORT
            WHERE NOM_AGENTE IS NOT NULL
            ORDER BY TRIM(NOM_AGENTE)
        ";

        return $this->query($sql)
            ->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }
}

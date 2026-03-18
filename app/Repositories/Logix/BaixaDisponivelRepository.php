<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class BaixaDisponivelRepository extends BaseLogixRepository
{
    public function search(array $params): Collection
    {
        $where = ['1=1'];
        $bindings = [];

        if (! empty($params['dat_conf_pgto_ini'])) {
            $where[] = "DAT_CONF_PGTO >= TO_DATE(:dat_conf_pgto_ini, 'YYYY-MM-DD')";
            $bindings['dat_conf_pgto_ini'] = $params['dat_conf_pgto_ini'];
        }

        if (! empty($params['dat_conf_pgto_fim'])) {
            $where[] = "DAT_CONF_PGTO <= TO_DATE(:dat_conf_pgto_fim, 'YYYY-MM-DD')";
            $bindings['dat_conf_pgto_fim'] = $params['dat_conf_pgto_fim'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT
                TRIM(INVOICE) AS invoice,
                TRIM(CLIENTE) AS cliente,
                TRIM(PAIS) AS pais,
                MOEDA,
                TRIM(NUM_OPE) AS num_ope,
                DAT_OPE,
                NOTA_FISCAL,
                DATA_NF,
                TRIM(COD_DUE) AS cod_due,
                TRIM(CHAVE_DUE) AS chave_due,
                VAL_PAGO,
                VAL_DESC_CONC,
                VAL_TARIFA,
                TRIM(FORM_PGTO) AS form_pgto,
                DAT_CONF_PGTO,
                COD_BANCO,
                TRIM(DEN_BANCO) AS den_banco
            FROM LOGIXPRD.VW_SC_BAIXA_DISPONIVEL
            WHERE {$whereClause}
            ORDER BY DAT_CONF_PGTO DESC, INVOICE
        ";

        return $this->query($sql, $bindings)
            ->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }
}

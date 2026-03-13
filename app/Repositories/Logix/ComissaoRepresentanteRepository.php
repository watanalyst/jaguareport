<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class ComissaoRepresentanteRepository extends BaseLogixRepository
{
    public function search(array $params): Collection
    {
        $bindings = [];
        $where = ['1=1', 'COD_REPRES_1 IS NOT NULL', 'COD_REPRES_1 != 0'];

        if (! empty($params['emp'])) {
            $where[] = 'EP = :emp';
            $bindings['emp'] = trim($params['emp']);
        }

        if (! empty($params['data_ini'])) {
            $where[] = "DAT_CREDITO >= TO_DATE(:data_ini, 'YYYY-MM-DD')";
            $bindings['data_ini'] = $params['data_ini'];
        }

        if (! empty($params['data_fim'])) {
            $where[] = "DAT_CREDITO <= TO_DATE(:data_fim, 'YYYY-MM-DD')";
            $bindings['data_fim'] = $params['data_fim'];
        }

        if (! empty($params['cod_repres'])) {
            $codes = array_filter(array_map('trim', explode(',', $params['cod_repres'])));
            if (count($codes) === 1) {
                $where[] = 'COD_REPRES_1 = :cod_repres';
                $bindings['cod_repres'] = $codes[0];
            } elseif (count($codes) > 1) {
                $placeholders = [];
                foreach ($codes as $i => $code) {
                    $key = "cod_repres_{$i}";
                    $placeholders[] = ":{$key}";
                    $bindings[$key] = $code;
                }
                $where[] = 'COD_REPRES_1 IN (' . implode(', ', $placeholders) . ')';
            }
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT
                TRIM(EP)                    AS emp,
                COD_REPRES_1                AS cod_repres,
                RAZ_SOCIAL                  AS nome_repres,
                TRUNC(DAT_CREDITO, 'MM')    AS mes_comissao,
                SUM(COMISSAO)               AS val_comissao
            FROM RELATORIOS.SC_COMISSAO1
            WHERE {$whereClause}
            GROUP BY
                EP,
                COD_REPRES_1,
                RAZ_SOCIAL,
                TRUNC(DAT_CREDITO, 'MM')
            ORDER BY
                COD_REPRES_1,
                TRUNC(DAT_CREDITO, 'MM')
        ";

        return $this->query($sql, $bindings)
            ->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }
}

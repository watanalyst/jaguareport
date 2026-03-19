<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class CambioPeriodoRepository extends BaseLogixRepository
{
    public function search(array $params): Collection
    {
        $where = ['1=1'];
        $bindings = [];

        if (! empty($params['cod_empresa'])) {
            $where[] = 'COD_EMPRESA = :cod_empresa';
            $bindings['cod_empresa'] = $params['cod_empresa'];
        }

        if (! empty($params['dat_cambio_ini'])) {
            $where[] = "DAT_CAMBIO >= TO_DATE(:dat_cambio_ini, 'YYYY-MM-DD')";
            $bindings['dat_cambio_ini'] = $params['dat_cambio_ini'];
        }

        if (! empty($params['dat_cambio_fim'])) {
            $where[] = "DAT_CAMBIO < TO_DATE(:dat_cambio_fim, 'YYYY-MM-DD') + 1";
            $bindings['dat_cambio_fim'] = $params['dat_cambio_fim'];
        }

        if (! empty($params['num_processo'])) {
            $where[] = 'NUM_PROCESSO = :num_processo';
            $bindings['num_processo'] = $params['num_processo'];
        }

        if (! empty($params['ano_processo'])) {
            $where[] = 'ANO_PROCESSO = :ano_processo';
            $bindings['ano_processo'] = $params['ano_processo'];
        }

        if (! empty($params['embarque'])) {
            $embarques = array_map('trim', explode(',', $params['embarque']));
            if (count($embarques) === 1) {
                $where[] = 'TRIM(EMBARQUE) = :embarque';
                $bindings['embarque'] = $embarques[0];
            } else {
                $placeholders = [];
                foreach ($embarques as $i => $emb) {
                    $key = "embarque_{$i}";
                    $placeholders[] = ":{$key}";
                    $bindings[$key] = $emb;
                }
                $where[] = 'TRIM(EMBARQUE) IN (' . implode(',', $placeholders) . ')';
            }
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT
                TRIM(TIPO) AS tipo,
                TRIM(COD_EMPRESA) AS cod_empresa,
                NUM_PROCESSO,
                ANO_PROCESSO,
                TRIM(EMBARQUE) AS embarque,
                TRIM(INVOICE) AS invoice,
                NOTA_FISCAL,
                DAT_CAMBIO,
                COD_CLIENTE,
                TRIM(NOM_CLIENTE) AS nom_cliente,
                TRIM(FORMA_PGTO) AS forma_pgto,
                TRIM(MOEDA) AS moeda,
                VAL_COT_MOEDA,
                VAL_MOEDA_EXT,
                VAL_REAIS,
                TRIM(BANCO_EXT) AS banco_ext,
                TRIM(COD_BANCO_CRED) AS banco_cred,
                TRIM(HISTORICO) AS historico
            FROM LOGIXPRD.VW_SC_EXP_CAMBIO_PERIODO
            WHERE {$whereClause}
            ORDER BY DAT_CAMBIO DESC, NUM_PROCESSO DESC
        ";

        return $this->query($sql, $bindings)
            ->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    public function distinctEmpresas(): Collection
    {
        return $this->query("
            SELECT DISTINCT TRIM(COD_EMPRESA) AS cod_empresa
            FROM LOGIXPRD.VW_SC_EXP_CAMBIO_PERIODO
            WHERE COD_EMPRESA IS NOT NULL
            ORDER BY TRIM(COD_EMPRESA)
        ")->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }
}

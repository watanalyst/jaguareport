<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class MovDevTerceirosRepository extends BaseLogixRepository
{
    public function search(array $params): Collection
    {
        $where = ['1=1'];
        $bindings = [];

        if (! empty($params['cod_empresa'])) {
            $empresas = array_map('trim', explode(',', $params['cod_empresa']));
            if (count($empresas) === 1) {
                $where[] = 'COD_EMPRESA = :cod_empresa';
                $bindings['cod_empresa'] = $empresas[0];
            } else {
                $placeholders = [];
                foreach ($empresas as $i => $emp) {
                    $key = "empresa_{$i}";
                    $placeholders[] = ":{$key}";
                    $bindings[$key] = $emp;
                }
                $where[] = 'COD_EMPRESA IN (' . implode(',', $placeholders) . ')';
            }
        }

        if (! empty($params['dat_movto'])) {
            $where[] = "DAT_MOVTO = TO_DATE(:dat_movto, 'YYYY-MM-DD')";
            $bindings['dat_movto'] = $params['dat_movto'];
        }

        if (! empty($params['cod_item'])) {
            $items = array_map('trim', explode(',', $params['cod_item']));
            if (count($items) === 1) {
                $where[] = 'TRIM(COD_ITEM) = :cod_item';
                $bindings['cod_item'] = $items[0];
            } else {
                $placeholders = [];
                foreach ($items as $i => $item) {
                    $key = "cod_item_{$i}";
                    $placeholders[] = ":{$key}";
                    $bindings[$key] = $item;
                }
                $where[] = 'TRIM(COD_ITEM) IN (' . implode(',', $placeholders) . ')';
            }
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT
                TRIM(COD_EMPRESA) AS cod_empresa,
                TRIM(COD_ITEM) AS cod_item,
                TRIM(ORIGEM) AS origem,
                DAT_MOVTO,
                NUM_DOCUM,
                NUM_SEQ,
                SAIDAS_ESTOQUE,
                SAIDAS_NF,
                DEVOL_ESTOQUE,
                DEVOL_NF,
                REFAT_ESTOQUE,
                REFAT_NF,
                BAIXA_ESTOQUE,
                BAIXA_NF,
                REM_TERC_ESTOQUE,
                REM_TERC_NF,
                RET_TERC_ESTOQUE,
                RET_TERC_NF
            FROM LOGIXPRD.VW_MOV_DEV_TERC
            WHERE {$whereClause}
            ORDER BY DAT_MOVTO DESC, COD_EMPRESA, COD_ITEM
        ";

        return $this->query($sql, $bindings)
            ->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    public function distinctEmpresas(): Collection
    {
        return $this->distinctEmpresasFromTable('LOGIXPRD.VW_MOV_DEV_TERC');
    }
}

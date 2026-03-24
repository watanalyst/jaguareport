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

        if (! empty($params['dat_movto_ini'])) {
            $where[] = "DAT_MOVTO >= TO_DATE(:dat_movto_ini, 'YYYY-MM-DD')";
            $bindings['dat_movto_ini'] = $params['dat_movto_ini'];
        }

        if (! empty($params['dat_movto_fim'])) {
            $where[] = "DAT_MOVTO <= TO_DATE(:dat_movto_fim, 'YYYY-MM-DD')";
            $bindings['dat_movto_fim'] = $params['dat_movto_fim'];
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

        // Usar oci_* nativo para contornar bug do PDO/oci8 que perde registros
        $pdo = $this->connection()->getPdo();
        $oci = $pdo->getAttribute(\PDO::ATTR_SERVER_INFO) !== false
            ? $this->searchWithOci($whereClause, $bindings)
            : null;

        if ($oci !== null) {
            return $oci;
        }

        // Fallback: query padrão
        $sql = "
            SELECT
                COD_EMPRESA,
                DAT_MOVTO,
                TRIM(COD_ITEM) AS COD_ITEM,
                SUM(SAIDAS_ESTOQUE) AS SAIDAS_ESTOQUE,
                SUM(SAIDAS_NF) AS SAIDAS_NF,
                SUM(DEVOL_ESTOQUE) AS DEVOL_ESTOQUE,
                SUM(DEVOL_NF) AS DEVOL_NF,
                SUM(REFAT_ESTOQUE) AS REFAT_ESTOQUE,
                SUM(REFAT_NF) AS REFAT_NF,
                SUM(BAIXA_ESTOQUE) AS BAIXA_ESTOQUE,
                SUM(BAIXA_NF) AS BAIXA_NF,
                SUM(REM_TERC_ESTOQUE) AS REM_TERC_ESTOQUE,
                SUM(REM_TERC_NF) AS REM_TERC_NF,
                SUM(RET_TERC_ESTOQUE) AS RET_TERC_ESTOQUE,
                SUM(RET_TERC_NF) AS RET_TERC_NF
            FROM LOGIXPRD.VW_MOV_DEV_TERC
            WHERE {$whereClause}
            GROUP BY COD_EMPRESA, DAT_MOVTO, TRIM(COD_ITEM)
            ORDER BY COD_EMPRESA, DAT_MOVTO, COD_ITEM
        ";

        return $this->query($sql, $bindings)
            ->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    private function searchWithOci(string $whereClause, array $bindings): ?Collection
    {
        try {
            $conn = $this->connection()->getPdo();

            // Obter o recurso OCI nativo do PDO
            // Usar exec direto com o recurso de conexão
            $dsn = config('database.connections.logix');
            $connStr = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST={$dsn['host']})(PORT={$dsn['port']}))(CONNECT_DATA=(SERVICE_NAME={$dsn['service_name']})))";

            $oci = oci_connect($dsn['username'], $dsn['password'], $connStr, $dsn['charset'] ?? 'AL32UTF8');

            if (! $oci) {
                return null;
            }

            $sql = "
                SELECT
                    COD_EMPRESA,
                    DAT_MOVTO,
                    TRIM(COD_ITEM) AS COD_ITEM,
                    SUM(SAIDAS_ESTOQUE) AS SAIDAS_ESTOQUE,
                    SUM(SAIDAS_NF) AS SAIDAS_NF,
                    SUM(DEVOL_ESTOQUE) AS DEVOL_ESTOQUE,
                    SUM(DEVOL_NF) AS DEVOL_NF,
                    SUM(REFAT_ESTOQUE) AS REFAT_ESTOQUE,
                    SUM(REFAT_NF) AS REFAT_NF,
                    SUM(BAIXA_ESTOQUE) AS BAIXA_ESTOQUE,
                    SUM(BAIXA_NF) AS BAIXA_NF,
                    SUM(REM_TERC_ESTOQUE) AS REM_TERC_ESTOQUE,
                    SUM(REM_TERC_NF) AS REM_TERC_NF,
                    SUM(RET_TERC_ESTOQUE) AS RET_TERC_ESTOQUE,
                    SUM(RET_TERC_NF) AS RET_TERC_NF
                FROM LOGIXPRD.VW_MOV_DEV_TERC
                WHERE {$whereClause}
                GROUP BY COD_EMPRESA, DAT_MOVTO, TRIM(COD_ITEM)
                ORDER BY COD_EMPRESA, DAT_MOVTO, COD_ITEM
            ";

            $stmt = oci_parse($oci, $sql);

            foreach ($bindings as $key => $value) {
                oci_bind_by_name($stmt, ":{$key}", $bindings[$key]);
            }

            oci_execute($stmt);

            $rows = [];
            while ($row = oci_fetch_assoc($stmt)) {
                $rows[] = (object) array_change_key_case($row, CASE_LOWER);
            }

            oci_free_statement($stmt);
            oci_close($oci);

            return collect($rows);
        } catch (\Throwable $e) {
            \Log::warning('OCI nativo falhou, usando PDO', ['error' => $e->getMessage()]);
            return null;
        }
    }
}

<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class ComissaoRepository extends BaseLogixRepository
{
    public function fetch(array $params): Collection
    {
        $where = [];
        $bindings = [];

        if (!empty($params['ep'])) {
            $where[] = 'TRIM(EP) = :ep';
            $bindings['ep'] = $params['ep'];
        }

        if (!empty($params['repres_ini'])) {
            $where[] = 'COD_REPRES_1 >= :repres_ini';
            $bindings['repres_ini'] = (int) $params['repres_ini'];
        }

        if (!empty($params['repres_fim'])) {
            $where[] = 'COD_REPRES_1 <= :repres_fim';
            $bindings['repres_fim'] = (int) $params['repres_fim'];
        }

        if (!empty($params['televendas'])) {
            $where[] = ($params['televendas'] === 'S')
                ? "UPPER(TRIM(RAZ_SOCIAL)) LIKE '%TELEVENDA%'"
                : "UPPER(TRIM(RAZ_SOCIAL)) NOT LIKE '%TELEVENDA%'";
        }

        if (!empty($params['clt'])) {
            $where[] = $params['clt'] === 'S'
                ? "TRIM(APELIDO) = 'CLT'"
                : "(TRIM(APELIDO) <> 'CLT' OR APELIDO IS NULL)";
        }

        if (!empty($params['fr']) && $params['fr'] !== 'AMBOS') {
            $where[] = 'TIP_FRETE = :fr';
            $bindings['fr'] = $params['fr'];
        }

        if (!empty($params['data_ini'])) {
            $where[] = "DAT_CREDITO >= TO_DATE(:data_ini, 'YYYY-MM-DD')";
            $bindings['data_ini'] = $params['data_ini'];
        }

        if (!empty($params['data_fim'])) {
            $where[] = "DAT_CREDITO <= TO_DATE(:data_fim, 'YYYY-MM-DD')";
            $bindings['data_fim'] = $params['data_fim'];
        }

        $sql = "
            SELECT
                TRIM(EP) AS EP,
                DEN_EMPRESA,
                EMPRESA AS EMP_REDUZ,
                COD_REPRES_1 AS COD_REPRES,
                RAZ_SOCIAL AS NOME_REPRES,
                NUM_DOCUM AS TITULO,
                NUM_DOCUM_ORIGEM AS NOTA,
                NUM_PLANO AS VDJ,
                CLIENTE AS COD_CLIENTE,
                NOM_CLIENTE AS NOME_CLIENTE,
                UF,
                DAT_EMIS AS DT_EMISSAO,
                DAT_VENCTO_S_DESC AS DT_VENCTO,
                DAT_CREDITO AS DT_CREDITO,
                COD_PORTADOR AS PTD,
                FM1 AS FP,
                TIP_FRETE AS FR,
                PESO_LIQUIDO AS PESO_NF,
                VAL_BRUTO AS VALOR,
                VAL_DESC_CONC AS VALOR_DESC,
                VAL_ABAT,
                VAL_PAGO,
                FRETE,
                VAL_IMPOSTO,
                VAL_LIQUIDO,
                PCT_COMIS_1 AS PCT_COMIS,
                COMISSAO,
                VAL_ORIGEM AS RECEBIDOS,
                CASE WHEN TRIM(APELIDO) = 'CLT' THEN 'S' ELSE 'N' END AS CLT,
                CASE WHEN TRIM(RAZ_SOCIAL) LIKE '%TELEVENDA%' THEN 'S' ELSE 'N' END AS TELEVENDAS
            FROM RELATORIOS.SC_COMISSAO1
        ";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return $this->query($sql, $bindings)
            ->map(fn($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }
}

<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class NotasFiscaisExportacaoRepository extends BaseLogixRepository
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

        if (! empty($params['dat_emissao_ini'])) {
            $where[] = "DAT_HOR_EMISSAO >= TO_DATE(:dat_emissao_ini, 'YYYY-MM-DD')";
            $bindings['dat_emissao_ini'] = $params['dat_emissao_ini'];
        }

        if (! empty($params['dat_emissao_fim'])) {
            $where[] = "DAT_HOR_EMISSAO < TO_DATE(:dat_emissao_fim, 'YYYY-MM-DD') + 1";
            $bindings['dat_emissao_fim'] = $params['dat_emissao_fim'];
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
                DAT_HOR_EMISSAO,
                COD_CLIENTE,
                TRIM(NOM_CLIENTE) AS nom_cliente,
                TRIM(FORMA_PGTO) AS forma_pgto,
                TRIM(MOEDA) AS moeda,
                VAL_COT_MOEDA,
                VAL_MOEDA_EXT,
                VAL_REAIS,
                TRIM(BANCO_EXT) AS banco_ext,
                TRIM(BANCO_CRED) AS banco_cred,
                TRIM(HISTORICO) AS historico
            FROM LOGIXPRD.VW_SC_EXP_NOTAS_FISCAIS
            WHERE {$whereClause}
            ORDER BY DAT_HOR_EMISSAO DESC, NUM_PROCESSO DESC
        ";

        return $this->query($sql, $bindings)
            ->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    public function distinctEmpresas(): Collection
    {
        return $this->distinctEmpresasFromTable('LOGIXPRD.VW_SC_EXP_NOTAS_FISCAIS');
    }
}

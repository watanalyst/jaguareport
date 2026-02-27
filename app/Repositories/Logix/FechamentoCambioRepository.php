<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class FechamentoCambioRepository extends BaseLogixRepository
{
    public function fetch(array $params): Collection
    {
        $where = [
            'COD_BANCO = :cod_banco',
            "DAT_CAMBIO = TO_DATE(:dat_cambio, 'YYYY-MM-DD')",
        ];
        $bindings = [
            'cod_banco'  => $params['cod_banco'],
            'dat_cambio' => $params['dat_cambio'],
        ];

        if (!empty($params['fech'])) {
            $where[] = 'FECH = :fech';
            $bindings['fech'] = $params['fech'];
        }

        if (!empty($params['ies_due'])) {
            $where[] = 'IES_DUE = :ies_due';
            $bindings['ies_due'] = $params['ies_due'];
        }

        if (!empty($params['num_seq_cambio'])) {
            $where[] = 'NUM_SEQ_CAMBIO = :num_seq_cambio';
            $bindings['num_seq_cambio'] = $params['num_seq_cambio'];
        }

        if (!empty($params['num_contrato'])) {
            $where[] = 'NUM_CONTRATO = :num_contrato';
            $bindings['num_contrato'] = $params['num_contrato'];
        }

        $sql = "
            SELECT
                TRIM(NUM_OPE) AS num_ope,
                DAT_CRED,
                TRIM(NOTA_FISCAL) AS nota_fiscal,
                DAT_NF,
                TRIM(DUE) AS due,
                TRIM(CHAVE_DUE) AS chave_due,
                TRIM(IMPORTADOR) AS importador,
                TRIM(ORDENANTE) AS ordenante,
                TRIM(TRADER) AS trader,
                TRIM(PAIS) AS pais,
                TRIM(MOEDA) AS moeda,
                VAL_INVOICE,
                VAL_COM_DESC,
                VAL_FECHAM,
                COMISSAO,
                DAT_CAMBIO,
                TRIM(INVOICE) AS invoice,
                TRIM(FECH) AS fech,
                COD_BANCO,
                TRIM(DEN_BANCO) AS den_banco,
                VAL_COTACAO,
                TRIM(BANK) AS bank,
                IES_DUE,
                COD_PORTADOR,
                TRIM(NOM_PORTADOR) AS nom_portador,
                TRIM(AGENCIA) AS agencia,
                TRIM(CONTA) AS conta,
                TRIM(FORMA_COMIS) AS forma_comis,
                TRIM(DEN_EMPRESA) AS den_empresa,
                TRIM(CNPJ) AS cnpj,
                NUM_SEQ_CAMBIO,
                TRIM(COD_FORMA_PGTO) AS cod_forma_pgto,
                TRIM(NUM_CONTRATO) AS num_contrato,
                TRIM(TIPO_CALC) AS tipo_calc
            FROM LOGIXPRD.VW_SC_EXP_CAMBIO
            WHERE " . implode(' AND ', $where) . "
            ORDER BY NUM_SEQ_CAMBIO, DAT_CRED
        ";

        return $this->query($sql, $bindings)
            ->map(fn($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }
}

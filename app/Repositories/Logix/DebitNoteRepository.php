<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class DebitNoteRepository extends BaseLogixRepository
{
    public function fetchHeader(string $codEmpresa, string $numNd, string $anoNd): ?object
    {
        $rows = $this->query("
            SELECT
                TRIM(COD_EMPRESA) AS cod_empresa,
                TRIM(NUM_ND) AS num_nd,
                TRIM(ANO_ND) AS ano_nd,
                TRIM(DEN_RAZAO_SOCIAL) AS den_razao_social,
                TRIM(END_EMPRESA) AS end_empresa,
                TRIM(COD_CEP) AS cod_cep,
                TRIM(DEN_MUNIC) AS den_munic,
                TRIM(UF) AS uf,
                TRIM(CNPJ) AS cnpj,
                TRIM(INS_ESTADUAL) AS ins_estadual,
                TRIM(COD_CLIENTE) AS cod_cliente,
                TRIM(NOM_CLIENTE) AS nom_cliente,
                TRIM(END_CLIENTE) AS end_cliente,
                TRIM(DEN_BAIRRO) AS den_bairro,
                VAL_TOTAL_ND,
                TRIM(COD_MOEDA) AS cod_moeda,
                TRIM(DEN_MOEDA_ABREV) AS den_moeda_abrev,
                DAT_INCLUSAO,
                TRIM(OBS) AS obs,
                TRIM(BANK) AS bank,
                TRIM(SWIFT_CODE_56) AS swift_code_56,
                TRIM(NUMBER_56) AS number_56,
                TRIM(ACCOUNT_57) AS account_57,
                TRIM(BRANCH_NUMBER) AS branch_number,
                TRIM(ACCOUNT_NUMBER) AS account_number,
                TRIM(IBAN) AS iban
            FROM LOGIXPRD.VW_SC_DEBIT_NOTE
            WHERE COD_EMPRESA = TRIM(:cod_empresa)
              AND NUM_ND = :num_nd
              AND ANO_ND = :ano_nd
              AND ROWNUM = 1
        ", [
            'cod_empresa' => $codEmpresa,
            'num_nd'      => $numNd,
            'ano_nd'      => $anoNd,
        ]);

        if ($rows->isEmpty()) {
            return null;
        }

        return (object) array_change_key_case((array) $rows->first(), CASE_LOWER);
    }

    public function fetchItems(string $codEmpresa, string $numNd, string $anoNd): Collection
    {
        return $this->query("
            SELECT
                TRIM(DESCRICAO_ND) AS descricao_nd,
                VAL_UNIT_ND,
                CASE WHEN COD_EMPRESA = '01' THEN 'JA'
                     WHEN COD_EMPRESA = '05' THEN 'FA'
                     WHEN COD_EMPRESA = '16' THEN 'UNI'
                     WHEN COD_EMPRESA = '17' THEN 'SAT'
                     WHEN COD_EMPRESA = '20' THEN 'AG'
                     WHEN COD_EMPRESA = '28' THEN 'SULL'
                     WHEN COD_EMPRESA = '40' THEN 'SULL'
                END AS cod_empresa_nd,
                TRIM(NUM_PROCESSO_ND) AS num_processo_nd,
                TRIM(ANO_PROCESSO_ND) AS ano_processo_nd,
                TRIM(EMBARQUE_ND) AS embarque_nd
            FROM LOGIXPRD.VW_SC_DEBIT_NOTE
            WHERE NUM_ND = :num_nd
              AND ANO_ND = :ano_nd
            ORDER BY NUM_SEQ
        ", [
            'num_nd' => $numNd,
            'ano_nd' => $anoNd,
        ])->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }
}

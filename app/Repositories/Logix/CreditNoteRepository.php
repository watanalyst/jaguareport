<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class CreditNoteRepository extends BaseLogixRepository
{
    public function fetchHeader(string $codEmpresa, string $numNc, string $anoNc): ?object
    {
        $rows = $this->query("
            SELECT
                TRIM(COD_EMPRESA) AS cod_empresa,
                TRIM(NUM_NC) AS num_nc,
                TRIM(ANO_NC) AS ano_nc,
                TRIM(DEN_RAZAO_SOCIAL) AS den_razao_social,
                TRIM(END_EMPRESA) AS end_empresa,
                TRIM(COD_CEP) AS cod_cep,
                TRIM(DEN_MUNIC) AS den_munic,
                TRIM(UF) AS uf,
                TRIM(CNPJ) AS cnpj,
                TRIM(INS_ESTADUAL) AS ins_estadual,
                TRIM(DETALHE_EMPRESA) AS detalhe_empresa,
                TRIM(COD_CLIENTE) AS cod_cliente,
                TRIM(NOM_CLIENTE) AS nom_cliente,
                TRIM(END_CLIENTE) AS end_cliente,
                TRIM(DEN_BAIRRO) AS den_bairro,
                VAL_TOTAL_NC,
                TRIM(COD_MOEDA) AS cod_moeda,
                TRIM(DEN_MOEDA_ABREV) AS den_moeda_abrev,
                DAT_INCLUSAO,
                TRIM(OBS) AS obs,
                TRIM(ACCOUNT_NAME_CLI) AS account_name_cli,
                TRIM(BANK_NAME_CLI) AS bank_name_cli,
                TRIM(ACCOUNT_TYPE_CLI) AS account_type_cli,
                TRIM(ACCOUNT_NUMBER_CLI) AS account_number_cli,
                TRIM(IBAN_CLI) AS iban_cli,
                TRIM(SWIFT_CODE_CLI) AS swift_code_cli,
                TRIM(BRANCH_CLI) AS branch_cli
            FROM LOGIXPRD.VW_SC_CREDIT_NOTE
            WHERE COD_EMPRESA = TRIM(:cod_empresa)
              AND NUM_NC = :num_nc
              AND ANO_NC = :ano_nc
              AND ROWNUM = 1
        ", [
            'cod_empresa' => $codEmpresa,
            'num_nc'      => $numNc,
            'ano_nc'      => $anoNc,
        ]);

        if ($rows->isEmpty()) {
            return null;
        }

        $row = $rows->first();

        return (object) array_change_key_case((array) $row, CASE_LOWER);
    }

    public function fetchItems(string $codEmpresa, string $numNc, string $anoNc): Collection
    {
        return $this->query("
            SELECT
                TRIM(DESCRICAO_NC) AS descricao_nc,
                VAL_UNIT_NC,
                VAL_TOTAL_NC,
                TRIM(COD_EMPRESA_NC) AS cod_empresa_nc,
                TRIM(NUM_PROCESSO_NC) AS num_processo_nc,
                TRIM(ANO_PROCESSO_NC) AS ano_processo_nc,
                TRIM(EMBARQUE_NC) AS embarque_nc
            FROM LOGIXPRD.VW_SC_CREDIT_NOTE
            WHERE COD_EMPRESA = TRIM(:cod_empresa)
              AND NUM_NC = :num_nc
              AND ANO_NC = :ano_nc
        ", [
            'cod_empresa' => $codEmpresa,
            'num_nc'      => $numNc,
            'ano_nc'      => $anoNc,
        ])->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }
}

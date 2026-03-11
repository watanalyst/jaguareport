<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class ProcessoExportacaoRepository extends BaseLogixRepository
{
    /**
     * Pesquisa processos de exportação para a grid (agrupado por processo/embarque).
     */
    public function search(array $params): Collection
    {
        $where  = ['1 = 1'];
        $bindings = [];

        if (! empty($params['empresa'])) {
            $where[] = 'EMPRESA = :empresa';
            $bindings['empresa'] = $params['empresa'];
        }

        if (! empty($params['num_processo'])) {
            $where[] = 'NUM_PROCESSO = :num_processo';
            $bindings['num_processo'] = (int) str_replace('.', '', $params['num_processo']);
        }

        if (! empty($params['ano_processo'])) {
            $where[] = 'ANO_PROCESSO = :ano_processo';
            $bindings['ano_processo'] = (int) str_replace('.', '', $params['ano_processo']);
        }

        if (! empty($params['dat_inclusao'])) {
            $where[] = "DAT_INCLUSAO = TO_DATE(:dat_inclusao, 'YYYY-MM-DD')";
            $bindings['dat_inclusao'] = $params['dat_inclusao'];
        }

        if (! empty($params['cod_situacao'])) {
            $where[] = 'COD_SITUACAO = :cod_situacao';
            $bindings['cod_situacao'] = $params['cod_situacao'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT
                EMPRESA AS cod_empresa,
                NUM_PROCESSO,
                EMBARQUE AS num_embarque,
                ANO_PROCESSO,
                MAX(TRIM(COD_SITUACAO)) AS cod_situacao,
                MAX(TRIM(SITUACAO)) AS situacao,
                MAX(DAT_INCLUSAO) AS dat_inclusao,
                MAX(TRIM(NOM_CLIENTE)) AS nom_cliente,
                MAX(TRIM(PAIS_DESTINO)) AS pais_destino,
                MAX(TRIM(INCOTERM)) AS incoterm,
                'S' AS ies_pf,
                MAX(CASE WHEN TRIM(CHECK_LIST) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_ckl,
                MAX(CASE WHEN TRIM(BL) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_bl,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_in,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_inf,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_fin,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_pl,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_dc,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_cr,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_sc,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_dpm,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_isf,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_pro,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_dec,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_hfc,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_mfc,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_fc,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_ndfc,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_cong,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_qc,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_afc,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_wc,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_ealnc,
                MAX(CASE WHEN TRIM(INVOICE) IS NOT NULL THEN 'S' ELSE 'N' END) AS ies_vgm
            FROM LOGIXPRD.VW_EXP_PROCESSOS
            WHERE {$whereClause}
            GROUP BY EMPRESA, NUM_PROCESSO, EMBARQUE, ANO_PROCESSO
            ORDER BY ANO_PROCESSO DESC, NUM_PROCESSO DESC, EMBARQUE
        ";

        return $this->query($sql, $bindings)
            ->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    /**
     * Busca dados de um processo/embarque específico para geração de PDF.
     */
    public function fetchDocumentData(string $empresa, string $processo, string $embarque, string $ano): Collection
    {
        $sql = "
            SELECT *
            FROM LOGIXPRD.VW_EXP_PROCESSOS
            WHERE EMPRESA       = :empresa
              AND NUM_PROCESSO   = :processo
              AND EMBARQUE       = :embarque
              AND ANO_PROCESSO   = :ano
        ";

        return $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'embarque' => $embarque,
            'ano'      => (int) str_replace('.', '', $ano),
        ])->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    /**
     * Busca dados do Check List (VW_SC_DOC_EXPORTACAO) — todas as colunas.
     */
    public function fetchCKLData(string $empresa, string $processo, string $ano, string $embarque): Collection
    {
        $sql = "
            SELECT *
            FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
            WHERE COD_EMPRESA  = :empresa
              AND NUM_PROCESSO = :processo
              AND ANO_PROCESSO = :ano
              AND EMBARQUE     = :embarque
        ";

        return $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    /**
     * Busca cabeçalho da Proforma (VW_SC_DOC_EXPORTACAO).
     */
    public function fetchProformaHeader(string $empresa, string $processo, string $ano): ?object
    {
        $sql = "
            SELECT * FROM (
                SELECT
                    COD_EMPRESA, PROFORMA, DEN_EMPRESA, NUM_CGC, END_EMPRESA, PAIS_INT,
                    DAT_INCLUSAO, COD_CEP, DEN_UNI_FEDER, DEN_MUNIC, SIF, PAIS_EMP,
                    MARCA, NUM_TELEFONE, NUM_FAX, SITE, CONTATO, EMAIL_CONTATO,
                    TEXTO1_BUYER, TEXTO2_BUYER, TEXTO3_BUYER, TEXTO4_BUYER, TEXTO5_BUYER,
                    ORDEM, DEN_CIDADE, DEN_PAIS, NOM_CONTATO, EMAIL,
                    COD_INCOTERM, DEN_INCOTERMS, DEN_MOEDA, COND_PGTO_INGLES,
                    PREV_EMBARQUE, CONT_TEMPERATURA, LOCAL_EMBARQUE, LOCAL_DESTINO,
                    FRT_INCOTERMS, PAIS_DESTINO, IES_INSP_PRESHIPT, PAID_BY,
                    IES_CERTIFICATES, PAID_BY1, IES_LEGALIZ, IES_HALAL,
                    DEN_MOEDA_ABREV, PCT_ADIANT, REF_CLIENTE_FINAL,
                    BANCO, BANCO1, BANCO2, BANCO3, BANCO4, BANCO5, BANCO6, BANCO7,
                    DETALHE_EMPRESA
                FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
                WHERE COD_EMPRESA = :empresa
                  AND NUM_PROCESSO = :processo
                  AND ANO_PROCESSO = :ano
            ) WHERE ROWNUM = 1
        ";

        $result = $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
        ])->first();

        return $result ? (object) array_change_key_case((array) $result, CASE_LOWER) : null;
    }

    /**
     * Busca itens da Proforma (VW_SC_DOC_EXPORTACAO).
     */
    public function fetchProformaItems(string $empresa, string $processo, string $ano): Collection
    {
        $sql = "
            SELECT
                EMBARQUE, COD_ITEM, DEN_ITEM_INT, QTD_PECAS_SOLIC,
                VAL_TONELADA, VAL_TOT_ITEM, DEN_MOEDA_ABREV
            FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
            WHERE COD_EMPRESA = :empresa
              AND NUM_PROCESSO = :processo
              AND ANO_PROCESSO = :ano
            ORDER BY EMBARQUE, COD_ITEM
        ";

        return $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
        ])->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    /**
     * Busca cabeçalho do BL (VW_SC_DOC_EXPORTACAO) + soma de comissão.
     */
    public function fetchBLHeader(string $empresa, string $processo, string $ano, string $embarque): ?object
    {
        $sql = "
            SELECT * FROM (
                SELECT
                    COD_EMPRESA, PROFORMA, NUM_CGC, END_EMPRESA, DEN_MUNIC, DEN_UNI_FEDER,
                    PAIS_EMP, NUM_TELEFONE, SITE, MARCA, DEN_MOEDA_ABREV, COND_PGTO_INGLES,
                    LOCAL_EMBARQUE, PORTO_TRANSBORDO, FRT_INCOTERMS, LOCAL_DESTINO, PAIS_DESTINO,
                    PCT_ADIANT, DAT_INCLUSAO, DAT_ATUALIZ, PREV_EMBARQUE, CONT_TEMPERATURA,
                    ORDEM, EM_NOME_DE, NUM_BL, DAT_EMBARQUE, DEN_RAZAO_SOCIAL, MADEIRA,
                    TEXTO1_NOTIFY, TEXTO2_NOTIFY, TEXTO3_NOTIFY, TEXTO4_NOTIFY, TEXTO5_NOTIFY,
                    TEXTO1_NOTIFY2, TEXTO2_NOTIFY2, TEXTO3_NOTIFY2, TEXTO4_NOTIFY2, TEXTO5_NOTIFY2,
                    REFER_SIG_SIF, IES_CSI_DSC,
                    TEXTO1_CONSIGNAT, TEXTO2_CONSIGNAT, TEXTO3_CONSIGNAT, TEXTO4_CONSIGNAT, TEXTO5_CONSIGNAT,
                    VGM, NOTA_FISCAL, COD_BOOKING, COD_CONTAINER, COD_LACRE, COD_LACRE_SIF,
                    TARA_CONT, NOM_AGENTE, DEN_NAVIO_AVIAO, ARMADOR_REDUZ,
                    TEXTO_INST_BL1, TEXTO_INST_BL2, TEXTO_INST_BL3, TEXTO_INST_BL4, TEXTO_INST_BL5,
                    VAL_FRETE_EMBARQUE, FORMA_COMISSAO, PCT_AGENTE, VAL_COMIS, PESO_PALETE,
                    CAMPO_OBS1, QTD_PECAS_SOLIC, QTD_PADR_EMBAL, RATEIO_PALETE,
                    COD_AGENTE_CARGA, NUM_AC, IES_TERMOGRAFO, EMBARQUE
                FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
                WHERE COD_EMPRESA  = :empresa
                  AND NUM_PROCESSO = :processo
                  AND ANO_PROCESSO = :ano
                  AND EMBARQUE     = :embarque
            ) WHERE ROWNUM = 1
        ";

        $result = $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->first();

        return $result ? (object) array_change_key_case((array) $result, CASE_LOWER) : null;
    }

    /**
     * Soma de comissão para o BL.
     */
    public function fetchBLTotComissao(string $empresa, string $processo, string $ano, string $embarque): float
    {
        $sql = "
            SELECT NVL(SUM(VAL_COMIS), 0) AS tot_comissao
            FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
            WHERE COD_EMPRESA  = :empresa
              AND NUM_PROCESSO = :processo
              AND ANO_PROCESSO = :ano
              AND EMBARQUE     = :embarque
        ";

        $result = $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->first();

        if (! $result) {
            return 0;
        }

        $row = array_change_key_case((array) $result, CASE_LOWER);

        return (float) ($row['tot_comissao'] ?? 0);
    }

    /**
     * Busca ITEM_AC para o BL.
     */
    public function fetchBLItemAC(string $empresa, string $processo, string $ano, string $embarque): string
    {
        $sql = "
            SELECT ITEM_AC
            FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
            WHERE COD_EMPRESA   = TRIM(:empresa)
              AND NUM_PROCESSO  = :processo
              AND ANO_PROCESSO  = :ano
              AND EMBARQUE      = :embarque
            ORDER BY ITEM_AC
        ";

        $rows = $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ]);

        return $rows->pluck('ITEM_AC')->filter()->implode(' / ');
    }

    /**
     * Busca COD_ITEM, NCM, CUBAGEM para seção CBM do BL.
     */
    public function fetchBLCubagem(string $empresa, string $processo, string $ano, string $embarque): Collection
    {
        $sql = "
            SELECT COD_ITEM, NCM, CUBAGEM
            FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
            WHERE COD_EMPRESA  = :empresa
              AND NUM_PROCESSO = :processo
              AND ANO_PROCESSO = :ano
              AND EMBARQUE     = :embarque
        ";

        return $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    /**
     * Busca itens do BL com join MATERIAL_EMBALAGEM (pesos por caixa + valores).
     */
    public function fetchBLItems(string $empresa, string $processo, string $ano, string $embarque): Collection
    {
        $sql = "
            SELECT
                DE.COD_ITEM,
                DE.NCM,
                DE.DEN_ITEM_INT,
                DE.TOT_EMBALAG,
                DE.QTD_PECAS_SOLIC,
                CASE WHEN ME.FL_UNDTIPOPESODEFIMATEEMBA = 'V'
                     THEN ROUND(DE.QTD_PECAS_SOLIC / DE.VOLUME_ITENS, 2)
                     ELSE DE.QTD_PADR_EMBAL
                END AS QTD_PADR_EMBAL,
                DE.PES_UNIT,
                CASE WHEN ME.FL_UNDTIPOPESODEFIMATEEMBA = 'V'
                     THEN ROUND(DE.RATEIO_PALETE / DE.VOLUME_ITENS, 2)
                     ELSE ROUND(DE.QTD_PADR_EMBAL + DE.PES_UNIT, 2)
                END AS QTD_BRUTO,
                ROUND(((DE.VAL_TOT_ITEM / DE.QTD_PECAS_SOLIC) * 1000), 2) AS VAL_TONELADA,
                DE.VAL_TOT_ITEM,
                DE.RATEIO_PALETE
            FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO DE
                LEFT JOIN MIMS_CORP.MATERIAL_EMBALAGEM@mimscorp ME
                    ON TRIM(DE.COD_ITEM) = TRIM(ME.IE_MATEEMBA)
            WHERE DE.EMBARQUE     = :embarque
              AND DE.COD_EMPRESA  = :empresa
              AND DE.ANO_PROCESSO = :ano
              AND DE.NUM_PROCESSO = :processo
        ";

        return $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    /**
     * Busca cabeçalho do Invoice Frete (VW_SC_DOC_EXPORTACAO).
     * Query mais simples que o Invoice — sem VAL_INVOICE, OPERACAO, COD_BANCO, etc.
     */
    public function fetchInvoiceFreteHeader(string $empresa, string $processo, string $ano, string $embarque): ?object
    {
        $sql = "
            SELECT * FROM (
                SELECT
                    COD_EMPRESA, PROFORMA, DEN_RAZAO_SOCIAL, NUM_CGC, END_EMPRESA,
                    COD_CEP, DEN_MUNIC, DEN_UNI_FEDER, PAIS_EMP, NUM_TELEFONE, NUM_FAX,
                    SITE, PAIS_INT, MARCA, COD_INCOTERM, DEN_MOEDA_ABREV,
                    COND_PGTO_INGLES, OBS_CND_PGTO, LOCAL_EMBARQUE, LOCAL_DESTINO,
                    PAIS_DESTINO, ORDEM, PCT_ADIANT, DAT_ATUALIZ, PREV_EMBARQUE,
                    NUM_BL, DAT_EMBARQUE, TRANSPORTADORA, EMBAL_INVOICE, QTDO_PALETE,
                    TEXTO1_CONSIGNAT, TEXTO2_CONSIGNAT, TEXTO3_CONSIGNAT,
                    TEXTO4_CONSIGNAT, TEXTO5_CONSIGNAT, COD_CONSIGNAT,
                    VGM, COD_CONTAINER, COD_LACRE, COD_LACRE_SIF,
                    DEN_NAVIO_AVIAO,
                    TEXTO_BL1, TEXTO_BL2, TEXTO_BL3, TEXTO_BL4, TEXTO_BL5,
                    VAL_FRETE_EMBARQUE, VAL_RECEB_ADTO, BANK, SWIFT_CODE_56,
                    ACCOUNT_57, SWIFT_CODE_57, ACCOUNT_NUMBER, IBAN,
                    TEXTO1_BUYER, TEXTO2_BUYER, TEXTO3_BUYER, TEXTO4_BUYER, TEXTO5_BUYER,
                    RATEIO_PALETE, TIP_EMBAL,
                    TEXTO_DOCS1, TEXTO_DOCS2, TEXTO_DOCS3,
                    BRANCH_NUMBER, EMBARQUE
                FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
                WHERE COD_EMPRESA  = :empresa
                  AND NUM_PROCESSO = :processo
                  AND ANO_PROCESSO = :ano
                  AND EMBARQUE     = :embarque
            ) WHERE ROWNUM = 1
        ";

        $result = $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->first();

        return $result ? (object) array_change_key_case((array) $result, CASE_LOWER) : null;
    }

    /**
     * Busca itens do Invoice Frete (query simples, sem join MATERIAL_EMBALAGEM).
     */
    public function fetchInvoiceFreteItems(string $empresa, string $processo, string $ano, string $embarque): Collection
    {
        $sql = "
            SELECT
                QTD_PECAS_SOLIC,
                RATEIO_PALETE,
                TOT_EMBALAG,
                VAL_TOT_ITEM,
                VAL_TONELADA,
                DEN_ITEM,
                DEN_ITEM_INT,
                VAL_FRETE_EMBARQUE,
                VAL_RECEB_ADTO
            FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
            WHERE COD_EMPRESA  = :empresa
              AND NUM_PROCESSO = :processo
              AND ANO_PROCESSO = :ano
              AND EMBARQUE     = :embarque
        ";

        return $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    /**
     * Busca cabeçalho do Dioxin/Radioactivity Certificate (VW_SC_DOC_EXPORTACAO).
     */
    public function fetchDCHeader(string $empresa, string $processo, string $ano, string $embarque): ?object
    {
        $sql = "
            SELECT * FROM (
                SELECT
                    COD_EMPRESA, PROFORMA, MARCA, DEN_RAZAO_SOCIAL, NUM_CGC,
                    NUM_TELEFONE, END_EMPRESA, DEN_MUNIC, DEN_UNI_FEDER, PAIS_EMP,
                    DAT_EMBARQUE, DAT_ATUALIZ,
                    TEXTO1_CONSIGNAT, TEXTO2_CONSIGNAT, TEXTO3_CONSIGNAT,
                    TEXTO4_CONSIGNAT, TEXTO5_CONSIGNAT,
                    DEN_NAVIO_AVIAO, LOCAL_EMBARQUE, PAIS_INT, LOCAL_DESTINO,
                    PORTO_TRANSBORDO, COD_CONTAINER, COD_LACRE, COD_LACRE_SIF,
                    SIF, TEXTO_BL1, EMBARQUE, ORDEM
                FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
                WHERE COD_EMPRESA  = :empresa
                  AND NUM_PROCESSO = :processo
                  AND ANO_PROCESSO = :ano
                  AND EMBARQUE     = :embarque
            ) WHERE ROWNUM = 1
        ";

        $result = $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->first();

        return $result ? (object) array_change_key_case((array) $result, CASE_LOWER) : null;
    }

    /**
     * Busca itens do Dioxin Certificate (descrição + peso líquido).
     */
    public function fetchDCItems(string $empresa, string $processo, string $ano, string $embarque): Collection
    {
        $sql = "
            SELECT
                DEN_ITEM_INT,
                QTD_PECAS_SOLIC
            FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
            WHERE COD_EMPRESA  = :empresa
              AND NUM_PROCESSO = :processo
              AND ANO_PROCESSO = :ano
              AND EMBARQUE     = :embarque
        ";

        return $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    /**
     * Busca cabeçalho do ISF 10+2 (VW_SC_DOC_EXPORTACAO).
     */
    public function fetchISFHeader(string $empresa, string $processo, string $ano, string $embarque): ?object
    {
        $sql = "
            SELECT * FROM (
                SELECT
                    COD_EMPRESA, PROFORMA, MARCA, DEN_RAZAO_SOCIAL, NUM_CGC,
                    NUM_TELEFONE, NUM_FAX, END_EMPRESA, COD_CEP,
                    DEN_MUNIC, DEN_UNI_FEDER, PAIS_EMP, PAIS_INT, SIF,
                    NUM_BL, DEN_NAVIO_AVIAO, ARMADOR, VOYAGE_NUMBER,
                    LOCAL_EMBARQUE, LOCAL_DESTINO, DAT_ETD, DAT_ETA,
                    COD_CONTAINER, ORDEM, DEN_ITEM_INT, IMPORT_PERMIT,
                    TEXTO1_CONSIGNAT, TEXTO2_CONSIGNAT, TEXTO3_CONSIGNAT,
                    TEXTO4_CONSIGNAT, TEXTO5_CONSIGNAT,
                    EMBARQUE
                FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
                WHERE COD_EMPRESA  = :empresa
                  AND NUM_PROCESSO = :processo
                  AND ANO_PROCESSO = :ano
                  AND EMBARQUE     = :embarque
            ) WHERE ROWNUM = 1
        ";

        $result = $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->first();

        return $result ? (object) array_change_key_case((array) $result, CASE_LOWER) : null;
    }

    /**
     * Busca cabeçalho do Protocolo (VW_SC_DOC_EXPORTACAO).
     */
    public function fetchPROHeader(string $empresa, string $processo, string $ano, string $embarque): ?object
    {
        $sql = "
            SELECT * FROM (
                SELECT
                    COD_EMPRESA, PROFORMA, MARCA, DEN_RAZAO_SOCIAL, NUM_CGC,
                    NUM_TELEFONE, PAIS_DESTINO, NUM_HALAL, IES_CSI_DSC,
                    EMBARQUE
                FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
                WHERE COD_EMPRESA  = :empresa
                  AND NUM_PROCESSO = :processo
                  AND ANO_PROCESSO = :ano
                  AND EMBARQUE     = :embarque
            ) WHERE ROWNUM = 1
        ";

        $result = $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->first();

        return $result ? (object) array_change_key_case((array) $result, CASE_LOWER) : null;
    }

    /**
     * Busca cabeçalho da Declaração (VW_SC_DOC_EXPORTACAO).
     */
    public function fetchDECHeader(string $empresa, string $processo, string $ano, string $embarque): ?object
    {
        $sql = "
            SELECT * FROM (
                SELECT
                    COD_EMPRESA, PROFORMA, MARCA, DEN_RAZAO_SOCIAL, NUM_CGC,
                    NUM_TELEFONE, DEN_MUNIC, COD_CONTAINER, REFER_SIG_SIF,
                    EMBARQUE
                FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
                WHERE COD_EMPRESA  = :empresa
                  AND NUM_PROCESSO = :processo
                  AND ANO_PROCESSO = :ano
                  AND EMBARQUE     = :embarque
            ) WHERE ROWNUM = 1
        ";

        $result = $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->first();

        return $result ? (object) array_change_key_case((array) $result, CASE_LOWER) : null;
    }

    /**
     * Busca COD_ITEM distintos do embarque (VW_SC_DOC_EXPORTACAO).
     */
    public function fetchEmbarqueItems(string $empresa, string $processo, string $ano, string $embarque): Collection
    {
        $sql = "
            SELECT DISTINCT TRIM(COD_ITEM) AS COD_ITEM
            FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
            WHERE COD_EMPRESA  = :empresa
              AND NUM_PROCESSO = :processo
              AND ANO_PROCESSO = :ano
              AND EMBARQUE     = :embarque
              AND COD_ITEM IS NOT NULL
        ";

        return $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    /**
     * Busca production date range (first/last) via MIMS para HFC.
     * Replica lógica do Scriptcase: busca itens via ID_INTEMATEEMBA, datas via MIMS only.
     */
    public function fetchHFCProductionDateRange(string $empresa, string $numPedido): ?array
    {
        $emp = trim($empresa);
        $pedidos = $this->buildPedidoList($numPedido);

        if (empty($pedidos) || ! isset(self::MIMS_LINKS[$emp])) {
            return null;
        }

        $m = self::MIMS_LINKS[$emp];
        $schema = $m['schema'];
        $link   = $m['link'];
        $inClause = implode(',', $pedidos);

        // Get distinct items from MIMS (using ID_INTEMATEEMBA, same as Scriptcase)
        $sqlItems = "
            SELECT DISTINCT M.ID_INTEMATEEMBA AS COD_ITEM
            FROM {$schema}.PEDIDO_VENDA@{$link} PV
                INNER JOIN {$schema}.EXPEDICAO_CARGA_IDENTIFICACAO@{$link} ECI
                    ON PV.ID_CARGEXPE = ECI.ID_CARGEXPE AND PV.FILIAL = ECI.FILIAL
                INNER JOIN {$schema}.PRODUCAO_REGISTRO@{$link} PR
                    ON ECI.ID_REGIPROD = PR.ID_REGIPROD AND ECI.FILIAL = PR.FILIAL
                INNER JOIN {$schema}.MATERIAL_EMBALAGEM@{$link} M
                    ON PR.ID_PRODMATEEMBA = M.ID_PRODMATEEMBA
            WHERE PV.IE_PEDIVEND IN ({$inClause})
              AND PV.FILIAL = {$m['filial']}
              AND M.ID_INTEMATEEMBA IS NOT NULL
        ";

        $items = $this->query($sqlItems, []);

        if ($items->isEmpty()) {
            return null;
        }

        $firstDate = null;
        $lastDate  = null;

        foreach ($items as $itemRow) {
            $codItem = trim(((array) $itemRow)['COD_ITEM'] ?? ((array) $itemRow)['cod_item'] ?? '');
            if (! $codItem) {
                continue;
            }

            // Query production dates for this item (same as Scriptcase sql_jagua/sql_ipu/sql_arap)
            $sqlDates = "
                SELECT
                    TO_CHAR(PR.DT_PADRREGIPROD, 'DD/MM/YYYY') AS PRODUCTION_DATE,
                    PR.DT_PADRREGIPROD
                FROM {$schema}.PEDIDO_VENDA@{$link} PV
                    INNER JOIN {$schema}.EXPEDICAO_CARGA_IDENTIFICACAO@{$link} ECI
                        ON PV.ID_CARGEXPE = ECI.ID_CARGEXPE AND PV.FILIAL = ECI.FILIAL
                    INNER JOIN {$schema}.PRODUCAO_REGISTRO@{$link} PR
                        ON ECI.ID_REGIPROD = PR.ID_REGIPROD AND ECI.FILIAL = PR.FILIAL
                    INNER JOIN {$schema}.MATERIAL_EMBALAGEM@{$link} M
                        ON PR.ID_PRODMATEEMBA = M.ID_PRODMATEEMBA
                WHERE PV.IE_PEDIVEND IN ({$inClause})
                  AND M.ID_INTEMATEEMBA = TRIM('{$codItem}')
                GROUP BY PR.DT_PADRREGIPROD, PR.DT_VALIREGIPROD,
                         PR.NR_LOTERASTREGIPROD, M.ID_INTEMATEEMBA,
                         M.FL_UNDTIPOPESODEFIMATEEMBA
            ";

            $dates = $this->query($sqlDates, []);

            if ($dates->isEmpty()) {
                continue;
            }

            // Same logic as Scriptcase: first row = first_date, last row = last_date
            $rows = $dates->values();
            $first = trim(((array) $rows->first())['PRODUCTION_DATE'] ?? ((array) $rows->first())['production_date'] ?? '');
            $last  = trim(((array) $rows->last())['PRODUCTION_DATE'] ?? ((array) $rows->last())['production_date'] ?? '');

            if ($first && $last) {
                $firstDate = $first;
                $lastDate  = $last;
            }
        }

        if (! $firstDate || ! $lastDate) {
            return null;
        }

        return ['first' => $firstDate, 'last' => $lastDate];
    }

    /**
     * Busca cabeçalho do Weight Certificate (VW_SC_DOC_EXPORTACAO).
     */
    public function fetchWCHeader(string $empresa, string $processo, string $ano, string $embarque): ?object
    {
        $sql = "
            SELECT * FROM (
                SELECT
                    COD_EMPRESA, PROFORMA, MARCA, DEN_RAZAO_SOCIAL, NUM_CGC,
                    NUM_TELEFONE, END_EMPRESA, DEN_MUNIC, DEN_UNI_FEDER, PAIS_EMP,
                    DAT_EMBARQUE, DAT_ATUALIZ,
                    TEXTO1_CONSIGNAT, TEXTO2_CONSIGNAT, TEXTO3_CONSIGNAT,
                    TEXTO4_CONSIGNAT, TEXTO5_CONSIGNAT,
                    DEN_NAVIO_AVIAO, LOCAL_EMBARQUE, PAIS_INT, LOCAL_DESTINO,
                    COD_CONTAINER, COD_LACRE, COD_LACRE_SIF,
                    SIF, NUM_BL, NUM_PEDIDO, EMBARQUE, ORDEM,
                    CONT_TEMPERATURA, PAIS_DESTINO
                FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
                WHERE COD_EMPRESA  = :empresa
                  AND NUM_PROCESSO = :processo
                  AND ANO_PROCESSO = :ano
                  AND EMBARQUE     = :embarque
            ) WHERE ROWNUM = 1
        ";

        $result = $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->first();

        return $result ? (object) array_change_key_case((array) $result, CASE_LOWER) : null;
    }

    /**
     * Busca cabeçalho do VGM (VW_SC_DOC_EXPORTACAO).
     */
    public function fetchVGMHeader(string $empresa, string $processo, string $ano, string $embarque): ?object
    {
        $sql = "
            SELECT * FROM (
                SELECT
                    COD_EMPRESA, PROFORMA, MARCA, DEN_RAZAO_SOCIAL, NUM_CGC,
                    NUM_TELEFONE, END_EMPRESA, DEN_MUNIC, DEN_UNI_FEDER, PAIS_EMP,
                    NUM_BL, COD_CONTAINER, TARA_CONT, RATEIO_PALETE, VGM,
                    DAT_ATUAL, EMBARQUE
                FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
                WHERE COD_EMPRESA  = :empresa
                  AND NUM_PROCESSO = :processo
                  AND ANO_PROCESSO = :ano
                  AND EMBARQUE     = :embarque
            ) WHERE ROWNUM = 1
        ";

        $result = $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->first();

        return $result ? (object) array_change_key_case((array) $result, CASE_LOWER) : null;
    }

    /**
     * Busca cabeçalho do Hormon Free Certificate (VW_SC_DOC_EXPORTACAO).
     */
    public function fetchHFCHeader(string $empresa, string $processo, string $ano, string $embarque): ?object
    {
        $sql = "
            SELECT * FROM (
                SELECT
                    COD_EMPRESA, PROFORMA, MARCA, DEN_RAZAO_SOCIAL, NUM_CGC,
                    NUM_TELEFONE, END_EMPRESA, DEN_MUNIC, DEN_UNI_FEDER, PAIS_EMP,
                    DAT_EMBARQUE, DAT_ATUALIZ,
                    TEXTO1_CONSIGNAT, TEXTO2_CONSIGNAT, TEXTO3_CONSIGNAT,
                    TEXTO4_CONSIGNAT, TEXTO5_CONSIGNAT,
                    DEN_NAVIO_AVIAO, LOCAL_EMBARQUE, PAIS_INT, LOCAL_DESTINO,
                    PORTO_TRANSBORDO, COD_CONTAINER, COD_LACRE, COD_LACRE_SIF,
                    SIF, TEXTO_BL1, NUM_BL, NUM_PEDIDO,
                    EMBARQUE, ORDEM
                FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
                WHERE COD_EMPRESA  = :empresa
                  AND NUM_PROCESSO = :processo
                  AND ANO_PROCESSO = :ano
                  AND EMBARQUE     = :embarque
            ) WHERE ROWNUM = 1
        ";

        $result = $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->first();

        return $result ? (object) array_change_key_case((array) $result, CASE_LOWER) : null;
    }

    // ── MIMS database links per company ────────────────────────────
    private const MIMS_LINKS = [
        '01' => ['schema' => 'MIMSJAGUA', 'link' => 'mimsjagua', 'filial' => 1],
        '28' => ['schema' => 'MIMSIPU',   'link' => 'mimsipu',   'filial' => 28],
        '20' => ['schema' => 'MIMSARAP',  'link' => 'mimsarap',  'filial' => 20],
        '43' => ['schema' => 'MIMSPALM',  'link' => 'mimspalm',  'filial' => 43],
    ];

    /**
     * Busca cabeçalho do Invoice (VW_SC_DOC_EXPORTACAO).
     */
    public function fetchInvoiceHeader(string $empresa, string $processo, string $ano, string $embarque): ?object
    {
        $sql = "
            SELECT * FROM (
                SELECT
                    COD_EMPRESA, PROFORMA, DEN_RAZAO_SOCIAL, NUM_CGC, END_EMPRESA,
                    COD_CEP, DEN_MUNIC, DEN_UNI_FEDER, PAIS_EMP, NUM_TELEFONE, NUM_FAX,
                    SITE, PAIS_INT, SIF, MARCA, COD_INCOTERM, DEN_MOEDA_ABREV,
                    COND_PGTO_INGLES, OBS_CND_PGTO, LOCAL_EMBARQUE, LOCAL_DESTINO,
                    PAIS_DESTINO, ORDEM, PCT_ADIANT, NUM_BL, DAT_EMBARQUE, IDIOMA,
                    TRANSPORTADORA, EMBAL_INVOICE, QTDO_PALETE, COD_CONSIGNAT,
                    TEXTO1_CONSIGNAT, TEXTO2_CONSIGNAT, TEXTO3_CONSIGNAT,
                    TEXTO4_CONSIGNAT, TEXTO5_CONSIGNAT,
                    VGM, COD_CONTAINER, COD_LACRE, COD_LACRE_SIF,
                    DEN_NAVIO_AVIAO,
                    TEXTO_BL1, TEXTO_BL2, TEXTO_BL3, TEXTO_BL4, TEXTO_BL5,
                    VAL_RECEB_ADTO, BANK, SWIFT_CODE_56, ACCOUNT_57, SWIFT_CODE_57,
                    ACCOUNT_NUMBER, IBAN,
                    TEXTO1_BUYER, TEXTO2_BUYER, TEXTO3_BUYER, TEXTO4_BUYER, TEXTO5_BUYER,
                    NUM_PEDIDO, DAT_ATUAL, TIP_EMBAL, VAL_INVOICE, OPERACAO,
                    COD_BANCO, EMAIL_CONTATO,
                    TEXTO_DOCS1, TEXTO_DOCS2, TEXTO_DOCS3,
                    IES_TERMOGRAFO, BRANCH_NUMBER, EMBARQUE
                FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
                WHERE COD_EMPRESA  = :empresa
                  AND NUM_PROCESSO = :processo
                  AND ANO_PROCESSO = :ano
                  AND EMBARQUE     = :embarque
            ) WHERE ROWNUM = 1
        ";

        $result = $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->first();

        return $result ? (object) array_change_key_case((array) $result, CASE_LOWER) : null;
    }

    /**
     * Busca NCMs distintos para o Invoice (concatenados com " / ").
     */
    public function fetchInvoiceNCM(string $empresa, string $processo, string $ano, string $embarque): string
    {
        $sql = "
            SELECT DISTINCT NCM
            FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
            WHERE COD_EMPRESA  = :empresa
              AND NUM_PROCESSO = :processo
              AND ANO_PROCESSO = :ano
              AND EMBARQUE     = :embarque
            ORDER BY NCM
        ";

        $rows = $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ]);

        return $rows
            ->map(fn ($row) => array_change_key_case((array) $row, CASE_LOWER))
            ->pluck('ncm')
            ->map(fn ($v) => trim($v))
            ->filter()
            ->unique()
            ->implode(' / ');
    }

    /**
     * Busca itens do Invoice com join MATERIAL_EMBALAGEM (pesos + valores).
     */
    public function fetchInvoiceItems(string $empresa, string $processo, string $ano, string $embarque): Collection
    {
        $sql = "
            SELECT
                DE.COD_ITEM,
                DE.DEN_ITEM,
                DE.DEN_ITEM_INT,
                DE.TOT_EMBALAG,
                DE.QTD_PECAS_SOLIC,
                CASE WHEN ME.FL_UNDTIPOPESODEFIMATEEMBA = 'V'
                     THEN ROUND(DE.QTD_PECAS_SOLIC / DE.VOLUME_ITENS, 2)
                     ELSE DE.QTD_PADR_EMBAL
                END AS QTD_PADR_EMBAL,
                DE.PES_UNIT,
                CASE WHEN ME.FL_UNDTIPOPESODEFIMATEEMBA = 'V'
                     THEN ROUND(DE.RATEIO_PALETE / DE.VOLUME_ITENS, 2)
                     ELSE ROUND(DE.QTD_PADR_EMBAL + DE.PES_UNIT, 2)
                END AS QTD_BRUTO,
                DE.VAL_TONELADA,
                DE.VAL_TOT_ITEM,
                DE.RATEIO_PALETE,
                DE.DEN_ITEM_INT AS DESC_PROD,
                DE.PRE_UNIT AS PRECO_UNIT,
                DE.NET_WEIGHT_BLOCK,
                DE.VAL_FRETE_EMBARQUE,
                DE.VAL_INVOICE,
                (DE.VAL_TOT_ITEM - NVL(DE.VAL_INVOICE, 0)) AS VAL_RESTANTE,
                DE.QTDO_PALETE
            FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO DE
                LEFT JOIN MIMS_CORP.MATERIAL_EMBALAGEM@mimscorp ME
                    ON TRIM(DE.COD_ITEM) = TRIM(ME.IE_MATEEMBA)
            WHERE DE.EMBARQUE     = :embarque
              AND DE.COD_EMPRESA  = :empresa
              AND DE.ANO_PROCESSO = :ano
              AND DE.NUM_PROCESSO = :processo
        ";

        return $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    // ═══════════════════════════════════════════════════════════════
    //  PACKING LIST (PL)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Busca cabeçalho do Packing List com LISTAGG de NUM_PEDIDO.
     */
    public function fetchPLHeader(string $empresa, string $processo, string $ano, string $embarque): ?object
    {
        $sql = "
            SELECT * FROM (
                SELECT
                    COD_EMPRESA, PROFORMA, DEN_RAZAO_SOCIAL, NUM_CGC, END_EMPRESA,
                    COD_CEP, DEN_MUNIC, DEN_UNI_FEDER, PAIS_EMP, NUM_TELEFONE, NUM_FAX,
                    PAIS_INT, MARCA,
                    TEXTO1_BUYER, TEXTO2_BUYER, TEXTO3_BUYER, TEXTO4_BUYER, TEXTO5_BUYER,
                    TIP_LOGRADOURO, DEN_CIDADE,
                    COND_PGTO_INGLES, LOCAL_EMBARQUE, LOCAL_DESTINO, PAIS_DESTINO,
                    ORDEM, QTDO_PALETE, EMBAL_INVOICE, TRANSPORTADORA, IMPORT_PERMIT,
                    DAT_EMBARQUE, IES_CSI_DSC, NUM_BL,
                    LISTAGG(DISTINCT NUM_PEDIDO, ',') WITHIN GROUP (ORDER BY NUM_PEDIDO) AS NUM_PEDIDO,
                    IDIOMA,
                    TEXTO1_CONSIGNAT, TEXTO2_CONSIGNAT, TEXTO3_CONSIGNAT,
                    TEXTO4_CONSIGNAT, TEXTO5_CONSIGNAT, COD_CONSIGNAT,
                    COD_CONTAINER, COD_LACRE, COD_LACRE_SIF, DEN_NAVIO_AVIAO,
                    TEXTO_BL1, TEXTO_BL2, TEXTO_BL3, TEXTO_BL4, TEXTO_BL5,
                    TIP_EMBAL, SITE, EMAIL_CONTATO,
                    TEXTO_DOCS1, TEXTO_DOCS2, TEXTO_DOCS3,
                    DAT_ATUAL, IES_TERMOGRAFO, EMBARQUE
                FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
                WHERE COD_EMPRESA  = :empresa
                  AND NUM_PROCESSO = :processo
                  AND ANO_PROCESSO = :ano
                  AND EMBARQUE     = :embarque
                GROUP BY
                    COD_EMPRESA, PROFORMA, DEN_RAZAO_SOCIAL, NUM_CGC, END_EMPRESA,
                    COD_CEP, DEN_MUNIC, DEN_UNI_FEDER, PAIS_EMP, NUM_TELEFONE, NUM_FAX,
                    PAIS_INT, MARCA,
                    TEXTO1_BUYER, TEXTO2_BUYER, TEXTO3_BUYER, TEXTO4_BUYER, TEXTO5_BUYER,
                    TIP_LOGRADOURO, DEN_CIDADE,
                    COND_PGTO_INGLES, LOCAL_EMBARQUE, LOCAL_DESTINO, PAIS_DESTINO,
                    ORDEM, QTDO_PALETE, EMBAL_INVOICE, TRANSPORTADORA, IMPORT_PERMIT,
                    DAT_EMBARQUE, IES_CSI_DSC, NUM_BL,
                    IDIOMA,
                    TEXTO1_CONSIGNAT, TEXTO2_CONSIGNAT, TEXTO3_CONSIGNAT,
                    TEXTO4_CONSIGNAT, TEXTO5_CONSIGNAT, COD_CONSIGNAT,
                    COD_CONTAINER, COD_LACRE, COD_LACRE_SIF, DEN_NAVIO_AVIAO,
                    TEXTO_BL1, TEXTO_BL2, TEXTO_BL3, TEXTO_BL4, TEXTO_BL5,
                    TIP_EMBAL, SITE, EMAIL_CONTATO,
                    TEXTO_DOCS1, TEXTO_DOCS2, TEXTO_DOCS3,
                    DAT_ATUAL, IES_TERMOGRAFO, EMBARQUE
            ) WHERE ROWNUM = 1
        ";

        $result = $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->first();

        return $result ? (object) array_change_key_case((array) $result, CASE_LOWER) : null;
    }

    /**
     * Itens resumo do Packing List (USA/Default — com join MATERIAL_EMBALAGEM).
     */
    public function fetchPLSummaryItems(string $empresa, string $processo, string $ano, string $embarque): Collection
    {
        $sql = "
            SELECT
                SUM(DE.TOT_EMBALAG) AS tot_embalag,
                SUM(DE.QTD_PECAS_SOLIC) AS qtd_pecas_solic,
                SUM(DE.RATEIO_PALETE) AS rateio_palete,
                CASE WHEN MAX(ME.QN_CAPAMEDIMATEEMBA) > 0
                     THEN ROUND(SUM(DE.QTD_PECAS_SOLIC) / SUM(DE.VOLUME_ITENS), 2)
                     ELSE DE.QTD_PADR_EMBAL
                END AS qtd_padr_embal,
                CASE WHEN MAX(ME.QN_CAPAMEDIMATEEMBA) > 0
                     THEN ROUND(SUM(DE.RATEIO_PALETE) / SUM(DE.VOLUME_ITENS), 2)
                     ELSE ROUND(DE.QTD_PADR_EMBAL + DE.PES_UNIT, 2)
                END AS qtd_bruto,
                DE.COD_ITEM,
                DE.DEN_ITEM_INT
            FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO DE
                LEFT JOIN MIMS_CORP.MATERIAL_EMBALAGEM@mimscorp ME
                    ON TRIM(DE.COD_ITEM) = TRIM(ME.IE_MATEEMBA)
            WHERE DE.COD_EMPRESA  = :empresa
              AND DE.NUM_PROCESSO = :processo
              AND DE.ANO_PROCESSO = :ano
              AND DE.EMBARQUE     = :embarque
            GROUP BY DE.QTD_PADR_EMBAL, DE.PES_UNIT, DE.COD_ITEM, DE.DEN_ITEM_INT, DE.NCM
            ORDER BY DE.NCM
        ";

        return $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    /**
     * Itens resumo do Packing List (variante espanhol — sem join).
     */
    public function fetchPLSummaryItemsSimple(string $empresa, string $processo, string $ano, string $embarque): Collection
    {
        $sql = "
            SELECT
                SUM(QTDO_PALETE) AS qtdo_palete,
                SUM(TOT_EMBALAG) AS tot_embalag,
                SUM(QTD_PECAS_SOLIC) AS qtd_pecas_solic,
                SUM(RATEIO_PALETE) AS rateio_palete,
                DEN_ITEM,
                DEN_ITEM_INT
            FROM LOGIXPRD.VW_SC_DOC_EXPORTACAO
            WHERE COD_EMPRESA  = :empresa
              AND NUM_PROCESSO = :processo
              AND ANO_PROCESSO = :ano
              AND EMBARQUE     = :embarque
            GROUP BY DEN_ITEM, DEN_ITEM_INT
        ";

        return $this->query($sql, [
            'empresa'  => $empresa,
            'processo' => (int) str_replace('.', '', $processo),
            'ano'      => (int) str_replace('.', '', $ano),
            'embarque' => $embarque,
        ])->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    /**
     * Itens distintos para o detalhe do Packing List (cross-DB MIMS + SC_TAB).
     */
    public function fetchPLDistinctItems(string $empresa, string $processo, string $ano, string $embarque, string $numPedido): Collection
    {
        $emp = trim($empresa);
        $pedidos = $this->buildPedidoList($numPedido);

        if (empty($pedidos)) {
            return collect();
        }

        $inClause = implode(',', $pedidos);
        $parts = [];

        // MIMS query (if company has a link)
        if (isset(self::MIMS_LINKS[$emp])) {
            $m = self::MIMS_LINKS[$emp];
            $schema = $m['schema'];
            $link   = $m['link'];
            $filial = $m['filial'];

            $parts[] = "
                SELECT DISTINCT M.IE_MATEEMBA AS COD_ITEM
                FROM {$schema}.PEDIDO_VENDA@{$link} PV
                    INNER JOIN {$schema}.EXPEDICAO_CARGA_IDENTIFICACAO@{$link} ECI
                        ON PV.ID_CARGEXPE = ECI.ID_CARGEXPE AND PV.FILIAL = ECI.FILIAL
                    INNER JOIN {$schema}.PRODUCAO_REGISTRO@{$link} PR
                        ON ECI.ID_REGIPROD = PR.ID_REGIPROD
                    INNER JOIN {$schema}.MATERIAL_EMBALAGEM@{$link} M
                        ON PR.ID_PRODMATEEMBA = M.ID_PRODMATEEMBA
                WHERE PV.IE_PEDIVEND IN ({$inClause})
                  AND PV.FILIAL = {$filial}
            ";
        }

        // SC_TAB_PACKING_LIST (always included)
        $parts[] = "
            SELECT DISTINCT TRIM(COD_ITEM) AS COD_ITEM
            FROM RELATORIOS.SC_TAB_PACKING_LIST_P
            WHERE NUM_PEDIDO IN ({$inClause})
              AND COD_EMPRESA = :empresa
        ";

        $sql = "SELECT DISTINCT COD_ITEM FROM (" . implode(' UNION ALL ', $parts) . ")
                WHERE COD_ITEM IS NOT NULL";

        return $this->query($sql, [
            'empresa' => $emp,
        ])->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    /**
     * Detalhe de produção do Packing List para um item específico (cross-DB MIMS + SC_TAB).
     */
    public function fetchPLPackingDetail(string $empresa, string $processo, string $ano, string $embarque, string $numPedido, string $codItem): Collection
    {
        $emp = trim($empresa);
        $pedidos = $this->buildPedidoList($numPedido);

        if (empty($pedidos)) {
            return collect();
        }

        $inClause = implode(',', $pedidos);
        $parts = [];

        // MIMS query (if company has a link)
        if (isset(self::MIMS_LINKS[$emp])) {
            $m = self::MIMS_LINKS[$emp];
            $schema = $m['schema'];
            $link   = $m['link'];

            $parts[] = "
                SELECT
                    TO_CHAR(PR.DT_PADRREGIPROD, 'DD/MM/YYYY') AS PRODUCTION_DATE,
                    TO_CHAR(PR.DT_VALIREGIPROD, 'DD/MM/YYYY') AS EXPIRY_DATE,
                    COUNT(PR.ID_REGIPROD) AS CARTONS,
                    CASE WHEN M.FL_UNDTIPOPESODEFIMATEEMBA <> 'V'
                         THEN SUM(PR.QN_PESOPADRREGIPROD)
                         ELSE SUM(PR.QN_PESOLIQUREGIPROD)
                    END AS NET_WEIGHT,
                    NVL(CI.GN_LOTEEXTECONTEMBAINDI,
                        CASE WHEN PR.NR_LOTERASTREGIPROD LIKE 'L%'
                             THEN SUBSTR(PR.NR_LOTERASTREGIPROD, 2, 2)
                             ELSE PR.NR_LOTERASTREGIPROD
                        END
                    ) AS LOTS,
                    NULL AS GROSS_WEIGHT,
                    PR.DT_PADRREGIPROD,
                    M.IE_MATEEMBA AS COD_ITEM_LOGIX
                FROM {$schema}.PEDIDO_VENDA@{$link} PV
                    INNER JOIN {$schema}.EXPEDICAO_CARGA_IDENTIFICACAO@{$link} ECI
                        ON PV.ID_CARGEXPE = ECI.ID_CARGEXPE AND PV.FILIAL = ECI.FILIAL
                    INNER JOIN {$schema}.PRODUCAO_REGISTRO@{$link} PR
                        ON ECI.ID_REGIPROD = PR.ID_REGIPROD
                    INNER JOIN {$schema}.MATERIAL_EMBALAGEM@{$link} M
                        ON PR.ID_PRODMATEEMBA = M.ID_PRODMATEEMBA
                    LEFT JOIN {$schema}.EMBALAGEM_CONTROLE_INDIVIDUAL@{$link} CI
                        ON PR.FILIAL = CI.FILIAL AND PR.ID_REGIPROD = CI.ID_REGIPROD
                WHERE PV.IE_PEDIVEND IN ({$inClause})
                  AND TRIM(M.IE_MATEEMBA) = TRIM(:cod_item_mims)
                GROUP BY
                    PR.DT_PADRREGIPROD, PR.DT_VALIREGIPROD,
                    PR.NR_LOTERASTREGIPROD, M.IE_MATEEMBA,
                    M.FL_UNDTIPOPESODEFIMATEEMBA,
                    CI.GN_LOTEEXTECONTEMBAINDI
            ";
        }

        // SC_TAB_PACKING_LIST (always included)
        $parts[] = "
            SELECT
                TO_CHAR(F.PRODUCTION_DATE, 'DD/MM/YYYY') AS PRODUCTION_DATE,
                TO_CHAR(F.DATE_EXPIRY, 'DD/MM/YYYY') AS EXPIRY_DATE,
                F.CARTONS,
                F.NET_WEIGHT,
                F.LOTS,
                F.GROSS_WEIGHT,
                F.PRODUCTION_DATE AS DT_PADRREGIPROD,
                P.COD_ITEM AS COD_ITEM_LOGIX
            FROM RELATORIOS.SC_TAB_PACKING_LIST_P P
                INNER JOIN RELATORIOS.SC_TAB_PACKING_LIST_F F
                    ON P.COD_TB_PL_P = F.COD_TB_PL_P
            WHERE P.COD_EMPRESA = :empresa
              AND P.NUM_PEDIDO IN ({$inClause})
              AND TRIM(P.COD_ITEM) = TRIM(:cod_item_tab)
        ";

        $sql = "SELECT * FROM (" . implode(' UNION ALL ', $parts) . ")
                ORDER BY TO_DATE(PRODUCTION_DATE, 'DD/MM/YYYY'), CARTONS";

        $bindings = ['empresa' => $emp, 'cod_item_tab' => $codItem];

        if (isset(self::MIMS_LINKS[$emp])) {
            $bindings['cod_item_mims'] = $codItem;
        }

        return $this->query($sql, $bindings)
            ->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    /**
     * Converte string CSV de NUM_PEDIDO em array de valores numéricos seguros para IN clause.
     */
    private function buildPedidoList(string $numPedido): array
    {
        return array_filter(
            array_map(fn ($v) => preg_match('/^\d+$/', trim($v)) ? trim($v) : null, explode(',', $numPedido))
        );
    }
}

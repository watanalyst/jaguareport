<?php

namespace App\Repositories\Logix;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class ComissaoRedeconomiaRepository extends BaseLogixRepository
{
    public function fetch(array $params): Collection
    {
        $sql = "
            SELECT
                EP,
                DEN_EMPRESA,
                EMPRESA,
                'REDECONOMIA' AS RAZ_SOCIAL,
                NUM_DOCUM AS TITULO,
                NUM_DOCUM_ORIGEM AS NOTA,
                NUM_PLANO AS VDJ,
                CLIENTE,
                NOM_CLIENTE,
                DEN_CIDADE,
                UF,
                DAT_EMIS,
                DAT_VENCTO_S_DESC,
                DAT_CREDITO,
                COD_PORTADOR AS PTD,
                FM1 AS FP,
                TIP_FRETE AS FR,
                PESO_LIQUIDO AS PESO_NF,
                VAL_BRUTO AS VALOR,
                VAL_DESC_CONC AS VALOR_DESC,
                VAL_ABAT,
                VAL_PAGO,
                0 AS FRETE,
                VAL_IMPOSTO,
                VAL_LIQUIDO,
                PCT_COMIS_1 AS PCT_COMIS,
                COMISSAO,
                VAL_ORIGEM AS RECEBIDOS
            FROM LOGIXPRD.VW_SC_COMISSAO_REDECONOMIA
            WHERE DAT_CREDITO BETWEEN TO_DATE(:data_ini, 'YYYY-MM-DD')
                                  AND TO_DATE(:data_fim, 'YYYY-MM-DD')
            ORDER BY
                EP,
                DAT_CREDITO,
                NUM_DOCUM
        ";

        $bindings = [
            'data_ini' => $params['data_ini'],
            'data_fim' => $params['data_fim'],
        ];

        return $this->query($sql, $bindings)
            ->map(function ($row) {
                $row = array_change_key_case((array) $row, CASE_LOWER);

                foreach (['dat_emis', 'dat_vencto_s_desc', 'dat_credito'] as $campo) {
                    if (!empty($row[$campo])) {
                        try {
                            $row[$campo] = Carbon::parse($row[$campo]);
                        } catch (\Throwable) {
                            // Mantém o valor original se falhar
                        }
                    }
                }

                return (object) $row;
            });
    }
}

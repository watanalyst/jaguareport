<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class EmpresaRepository extends BaseLogixRepository
{
    public function all(): Collection
    {
        return Cache::remember('logix_empresas', 3600, function () {
            return $this->query("
                SELECT
                    TRIM(COD_EMPRESA) AS ep,
                    TRIM(DEN_EMPRESA) AS empresa,
                    TRIM(DEN_REDUZ) AS den_reduz,
                    TRIM(DEN_MUNIC) AS municipio,
                    UNI_FEDER AS uf
                FROM LOGIXPRD.EMPRESA
                ORDER BY COD_EMPRESA ASC
            ");
        });
    }
}

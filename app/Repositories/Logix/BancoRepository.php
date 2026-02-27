<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class BancoRepository extends BaseLogixRepository
{
    public function all(): Collection
    {
        return $this->query("
            SELECT
                COD_BANCO AS COD_BANCO,
                (COD_BANCO || ' - ' || DEN_BANCO) AS BANCO
            FROM LOGIXPRD.EXP_BANCO
            ORDER BY COD_BANCO, DEN_BANCO
        ");
    }
}

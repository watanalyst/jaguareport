<?php

namespace App\Repositories\Logix;

use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

abstract class BaseLogixRepository
{
    protected function connection(): Connection
    {
        return DB::connection('logix');
    }

    protected function query(string $sql, array $bindings = []): Collection
    {
        return collect($this->connection()->select($sql, $bindings));
    }

    /**
     * Retorna empresas distintas de uma view/tabela com DEN_REDUZ da LOGIXPRD.EMPRESA.
     */
    protected function distinctEmpresasFromTable(string $table, string $column = 'COD_EMPRESA'): Collection
    {
        $cacheKey = 'distinct_empresas_' . md5($table . $column);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 1800, function () use ($table, $column) {
            return $this->query("
                SELECT DISTINCT
                    TRIM(v.{$column}) AS ep,
                    TRIM(e.DEN_REDUZ) AS den_reduz
                FROM {$table} v
                JOIN LOGIXPRD.EMPRESA e ON TRIM(v.{$column}) = TRIM(e.COD_EMPRESA)
                WHERE v.{$column} IS NOT NULL
                ORDER BY TRIM(v.{$column})
            ")->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
        });
    }
}

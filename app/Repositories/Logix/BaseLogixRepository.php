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
}

<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class PermissionRepository extends BaseLogixRepository
{
    public function getAccessibleApps(string $login): Collection
    {
        return $this->query("
            SELECT TRIM(APP_NAME) AS app_name
            FROM RELATORIOS.SEC_USERS_APPS
            WHERE LOGIN = :login
              AND PRIV_ACCESS = 'Y'
        ", ['login' => $login])->pluck('app_name');
    }
}

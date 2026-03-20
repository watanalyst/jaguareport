<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class EmpresaRepository extends BaseLogixRepository
{
    public function empresasPorUsuario(): Collection
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $login = $user->sc_user;
        $key = "empresas_usuario_{$login}";

        if (session()->has($key)) {
            return collect(session()->get($key));
        }

        $empresas = $this->query("
            SELECT
                TRIM(EJ.COD_EMPRESA) AS ep,
                TRIM(E.DEN_REDUZ) AS den_reduz
            FROM RELATORIOS.SEC_USERS_EMPRESAS EJ
            JOIN LOGIXPRD.EMPRESA E
                ON E.COD_EMPRESA = EJ.COD_EMPRESA
            WHERE EJ.LOGIN = :login
              AND EJ.COD_EMPRESA IS NOT NULL
            ORDER BY EJ.COD_EMPRESA
        ", ['login' => $login]);

        session()->put($key, $empresas->all());

        return $empresas;
    }
}

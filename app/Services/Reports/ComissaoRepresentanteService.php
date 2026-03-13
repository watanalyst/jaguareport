<?php

namespace App\Services\Reports;

use App\Repositories\Logix\ComissaoRepresentanteRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ComissaoRepresentanteService
{
    public function __construct(
        private ComissaoRepresentanteRepository $repository,
    ) {}

    private function oracle()
    {
        return DB::connection('logix');
    }

    public function search(array $params): Collection
    {
        $oracleData = $this->repository->search($params);

        if ($oracleData->isEmpty()) {
            return $oracleData;
        }

        try {
            $query = $this->oracle()->table('LOGIXPRD.SC_COMISSAO_REPRESENTANTE');
            if (! empty($params['emp'])) {
                $query->where('EMP', trim($params['emp']));
            }
            $aprovados = $query->get()
                ->keyBy(fn ($r) => trim($r->emp) . '|' . trim($r->cod_repres) . '|' . date('Y-m-d', strtotime($r->mes_comissao)));
        } catch (\Throwable) {
            $aprovados = collect();
        }

        $result = $oracleData->map(function ($row) use ($aprovados) {
            $mesFormatted = date('Y-m-d', strtotime($row->mes_comissao));
            $key = trim($row->emp) . '|' . trim($row->cod_repres) . '|' . $mesFormatted;

            $aprov = $aprovados->get($key);

            $row->status_aprov   = $aprov?->status_aprov ?? 'N';
            $row->data_aprovacao = $aprov?->data_aprovacao;
            $row->usuario_aprov  = $aprov?->usuario_aprov;

            return $row;
        });

        $statusFilter = $params['status'] ?? null;
        if ($statusFilter) {
            $result = $result->filter(fn ($row) => $row->status_aprov === $statusFilter);
        }

        return $result->values();
    }

    public function aprovar(array $registros, string $usuario): int
    {
        $count = 0;

        foreach ($registros as $reg) {
            $exists = $this->oracle()
                ->table('LOGIXPRD.SC_COMISSAO_REPRESENTANTE')
                ->where('EMP', $reg['emp'])
                ->where('COD_REPRES', $reg['cod_repres'])
                ->where('MES_COMISSAO', $reg['mes_comissao'])
                ->exists();

            if ($exists) {
                $this->oracle()
                    ->table('LOGIXPRD.SC_COMISSAO_REPRESENTANTE')
                    ->where('EMP', $reg['emp'])
                    ->where('COD_REPRES', $reg['cod_repres'])
                    ->where('MES_COMISSAO', $reg['mes_comissao'])
                    ->update([
                        'NOME_REPRES'    => $reg['nome_repres'] ?? null,
                        'VAL_COMISSAO'   => $reg['val_comissao'],
                        'STATUS_APROV'   => 'S',
                        'DATA_APROVACAO' => now()->toDateString(),
                        'USUARIO_APROV'  => $usuario,
                    ]);
            } else {
                $this->oracle()
                    ->table('LOGIXPRD.SC_COMISSAO_REPRESENTANTE')
                    ->insert([
                        'EMP'            => $reg['emp'],
                        'COD_REPRES'     => $reg['cod_repres'],
                        'MES_COMISSAO'   => $reg['mes_comissao'],
                        'NOME_REPRES'    => $reg['nome_repres'] ?? null,
                        'VAL_COMISSAO'   => $reg['val_comissao'],
                        'STATUS_APROV'   => 'S',
                        'DATA_APROVACAO' => now()->toDateString(),
                        'USUARIO_APROV'  => $usuario,
                    ]);
            }
            $count++;
        }

        return $count;
    }

    public function desaprovar(array $registros): int
    {
        $count = 0;

        foreach ($registros as $reg) {
            $affected = $this->oracle()
                ->table('LOGIXPRD.SC_COMISSAO_REPRESENTANTE')
                ->where('EMP', $reg['emp'])
                ->where('COD_REPRES', $reg['cod_repres'])
                ->where('MES_COMISSAO', $reg['mes_comissao'])
                ->delete();
            $count += $affected;
        }

        return $count;
    }
}

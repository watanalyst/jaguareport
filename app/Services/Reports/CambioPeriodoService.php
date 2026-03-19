<?php

namespace App\Services\Reports;

use App\Repositories\Logix\CambioPeriodoRepository;
use Illuminate\Support\Collection;

class CambioPeriodoService
{
    public function __construct(
        private CambioPeriodoRepository $repository,
    ) {}

    public function search(array $params): Collection
    {
        return $this->repository->search($params);
    }

    public function distinctEmpresas(): Collection
    {
        return $this->repository->distinctEmpresas();
    }
}

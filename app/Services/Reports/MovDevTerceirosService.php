<?php

namespace App\Services\Reports;

use App\Repositories\Logix\MovDevTerceirosRepository;
use Illuminate\Support\Collection;

class MovDevTerceirosService
{
    public function __construct(
        private MovDevTerceirosRepository $repository,
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

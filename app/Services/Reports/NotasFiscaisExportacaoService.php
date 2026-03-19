<?php

namespace App\Services\Reports;

use App\Repositories\Logix\NotasFiscaisExportacaoRepository;
use Illuminate\Support\Collection;

class NotasFiscaisExportacaoService
{
    public function __construct(
        private NotasFiscaisExportacaoRepository $repository,
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

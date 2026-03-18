<?php

namespace App\Services\Reports;

use App\Repositories\Logix\ComissaoExportacaoRepository;
use Illuminate\Support\Collection;

class ComissaoExportacaoService
{
    public function __construct(
        private ComissaoExportacaoRepository $repository,
    ) {}

    public function search(array $params): Collection
    {
        return $this->repository->search($params);
    }

    public function distinctAgentes(): Collection
    {
        return $this->repository->distinctAgentes();
    }
}

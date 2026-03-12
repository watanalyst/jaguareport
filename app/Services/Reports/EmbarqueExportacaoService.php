<?php

namespace App\Services\Reports;

use App\Repositories\Logix\EmbarqueExportacaoRepository;
use Illuminate\Support\Collection;

class EmbarqueExportacaoService
{
    public function __construct(
        private EmbarqueExportacaoRepository $repository,
    ) {}

    public function search(array $params): Collection
    {
        return $this->repository->search($params);
    }

    public function distinctItems(string $empresa): Collection
    {
        return $this->repository->distinctItems($empresa);
    }
}

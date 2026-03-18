<?php

namespace App\Services\Reports;

use App\Repositories\Logix\BaixaDisponivelRepository;
use Illuminate\Support\Collection;

class BaixaDisponivelService
{
    public function __construct(
        private BaixaDisponivelRepository $repository,
    ) {}

    public function search(array $params): Collection
    {
        return $this->repository->search($params);
    }
}

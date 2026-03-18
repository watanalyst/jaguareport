<?php

namespace App\Services\Reports;

use App\Repositories\Logix\BancoCreditNoteRepository;
use Illuminate\Support\Collection;

class BancoCreditNoteService
{
    public function __construct(
        private BancoCreditNoteRepository $repository,
    ) {}

    public function all(): Collection
    {
        return $this->repository->all();
    }

    public function search(array $params): Collection
    {
        return $this->repository->search($params);
    }

    public function find(int $id): ?object
    {
        return $this->repository->find($id);
    }

    public function store(array $data, string $usuario): int
    {
        $data['usuario'] = $usuario;

        return $this->repository->insert($data);
    }

    public function update(int $id, array $data, string $usuario): void
    {
        $data['usuario'] = $usuario;
        $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}

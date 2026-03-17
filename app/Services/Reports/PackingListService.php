<?php

namespace App\Services\Reports;

use App\Repositories\Logix\PackingListRepository;
use Illuminate\Support\Collection;

class PackingListService
{
    public function __construct(
        private PackingListRepository $repository,
    ) {}

    public function search(array $params): Collection
    {
        return $this->repository->search($params);
    }

    public function getMasterWithDetails(int $id): array
    {
        $master = $this->repository->findMaster($id);

        if (! $master) {
            abort(404, 'Packing List não encontrado.');
        }

        $details = $this->repository->getDetails($id);

        return [
            'master'  => $master,
            'details' => $details,
        ];
    }

    public function storeMaster(array $data, string $usuario): int
    {
        if ($this->repository->masterExists($data['cod_empresa'], $data['processo'], $data['ano'], $data['embarque'], $data['num_pedido'], $data['cod_item'])) {
            abort(response()->json(['message' => 'Já existe um Packing List com este pedido e item.'], 422));
        }

        return $this->repository->insertMaster([
            ...$data,
            'usuario' => $usuario,
        ]);
    }

    public function updateMaster(int $id, array $data, string $usuario): void
    {
        if ($this->repository->masterExists($data['cod_empresa'], $data['processo'], $data['ano'], $data['embarque'], $data['num_pedido'], $data['cod_item'], $id)) {
            abort(response()->json(['message' => 'Já existe um Packing List com este pedido e item.'], 422));
        }

        $this->repository->updateMaster($id, [
            ...$data,
            'usuario' => $usuario,
        ]);
    }

    public function deleteMaster(int $id): void
    {
        $this->repository->deleteMaster($id);
    }

    public function storeDetail(int $masterId, array $data, string $usuario): int
    {
        return $this->repository->insertDetail($masterId, [
            ...$data,
            'usuario' => $usuario,
        ]);
    }

    public function updateDetail(int $detailId, array $data, string $usuario): void
    {
        $this->repository->updateDetail($detailId, [
            ...$data,
            'usuario' => $usuario,
        ]);
    }

    public function deleteDetail(int $detailId): void
    {
        $this->repository->deleteDetail($detailId);
    }
}

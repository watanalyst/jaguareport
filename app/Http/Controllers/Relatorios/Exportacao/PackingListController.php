<?php

namespace App\Http\Controllers\Relatorios\Exportacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relatorios\Exportacao\PackingListDetailRequest;
use App\Http\Requests\Relatorios\Exportacao\PackingListMasterRequest;
use App\Http\Requests\Relatorios\Exportacao\PackingListSearchRequest;
use App\Repositories\Logix\EmpresaRepository;
use App\Services\Reports\PackingListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PackingListController extends Controller
{
    public function __construct(
        private PackingListService $service,
        private EmpresaRepository $empresaRepo,
    ) {}

    /**
     * Page: list/search packing lists.
     */
    public function index(): Response
    {
        $allowedEmpresas = ['01', '05', '20', '28', '43'];

        try {
            $empresas = $this->empresaRepo->all()->filter(
                fn ($e) => in_array(trim($e->ep), $allowedEmpresas)
            )->values();
        } catch (\Throwable) {
            $empresas = collect();
        }

        $repo = app(\App\Repositories\Logix\PackingListRepository::class);

        try {
            $initialData = $repo->getRecent();
        } catch (\Throwable) {
            $initialData = collect();
        }

        return Inertia::render('Relatorios/Exportacao/PackingList/Index', [
            'empresas'    => $empresas,
            'initialData' => $initialData,
        ]);
    }

    /**
     * API: search master records.
     */
    public function pesquisar(PackingListSearchRequest $request): JsonResponse
    {
        $data = $this->service->search($request->validated());

        return response()->json(['data' => $data]);
    }

    /**
     * API: get master + details for editing.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->service->getMasterWithDetails($id);

        return response()->json($data);
    }

    /**
     * API: create master record.
     */
    public function store(PackingListMasterRequest $request): JsonResponse
    {
        $usuario = $request->user()->sc_user ?? $request->user()->name;
        $id = $this->service->storeMaster($request->validated(), $usuario);

        return response()->json(['id' => $id, 'message' => 'Packing List criado com sucesso.'], 201);
    }

    /**
     * API: update master record.
     */
    public function update(PackingListMasterRequest $request, int $id): JsonResponse
    {
        $usuario = $request->user()->sc_user ?? $request->user()->name;
        $this->service->updateMaster($id, $request->validated(), $usuario);

        return response()->json(['message' => 'Packing List atualizado com sucesso.']);
    }

    /**
     * API: delete master + details.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->deleteMaster($id);

        return response()->json(['message' => 'Packing List excluído com sucesso.']);
    }

    /**
     * API: lookup NUM_PEDIDO and COD_ITEM from EXP_ITENS.
     */
    public function lookup(Request $request): JsonResponse
    {
        $repo = app(\App\Repositories\Logix\PackingListRepository::class);
        $result = [];

        if ($request->filled(['cod_empresa', 'processo', 'ano', 'embarque'])) {
            $result['pedidos'] = $repo->listPedidos(
                $request->input('cod_empresa'),
                $request->input('processo'),
                $request->input('ano'),
                $request->input('embarque')
            )->pluck('num_pedido')->values();
        }

        if ($request->filled('num_pedido')) {
            $result['itens'] = $repo->listItensByPedido(
                (int) $request->input('num_pedido')
            )->pluck('cod_item')->values();
        }

        return response()->json($result);
    }

    /**
     * API: add detail row.
     */
    public function storeDetail(PackingListDetailRequest $request, int $masterId): JsonResponse
    {
        $usuario = $request->user()->sc_user ?? $request->user()->name;
        $id = $this->service->storeDetail($masterId, $request->validated(), $usuario);

        return response()->json(['id' => $id, 'message' => 'Item adicionado com sucesso.'], 201);
    }

    /**
     * API: update detail row.
     */
    public function updateDetail(PackingListDetailRequest $request, int $masterId, int $detailId): JsonResponse
    {
        $usuario = $request->user()->sc_user ?? $request->user()->name;
        $this->service->updateDetail($detailId, $request->validated(), $usuario);

        return response()->json(['message' => 'Item atualizado com sucesso.']);
    }

    /**
     * API: delete detail row.
     */
    public function destroyDetail(int $masterId, int $detailId): JsonResponse
    {
        $this->service->deleteDetail($detailId);

        return response()->json(['message' => 'Item excluído com sucesso.']);
    }
}

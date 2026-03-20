<?php

namespace App\Http\Controllers\Relatorios\Exportacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relatorios\Exportacao\BancoCreditNoteRequest;
use App\Repositories\Logix\EmpresaRepository;
use App\Services\Reports\BancoCreditNoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BancoCreditNoteController extends Controller
{
    public function __construct(
        private BancoCreditNoteService $service,
        private EmpresaRepository $empresaRepo,
    ) {}

    public function index()
    {
        try {
            $empresas = $this->empresaRepo->empresasPorUsuario();
        } catch (\Throwable) {
            $empresas = collect();
        }

        try {
            $initialData = $this->service->all();
        } catch (\Throwable) {
            $initialData = collect();
        }

        return Inertia::render('Relatorios/Exportacao/BancoCreditNote/Index', [
            'empresas'    => $empresas,
            'initialData' => $initialData,
        ]);
    }

    public function pesquisar(Request $request): JsonResponse
    {
        $data = $this->service->search($request->only(['cod_empresa', 'num_nc', 'ano_nc']));

        return response()->json(['data' => $data]);
    }

    public function show(int $id): JsonResponse
    {
        $record = $this->service->find($id);

        if (! $record) {
            return response()->json(['message' => 'Registro não encontrado.'], 404);
        }

        return response()->json($record);
    }

    public function store(BancoCreditNoteRequest $request): JsonResponse
    {
        $usuario = $request->user()->sc_user ?? $request->user()->name;
        $id = $this->service->store($request->validated(), $usuario);

        return response()->json(['id' => $id, 'message' => 'Registro criado com sucesso.'], 201);
    }

    public function update(BancoCreditNoteRequest $request, int $id): JsonResponse
    {
        $usuario = $request->user()->sc_user ?? $request->user()->name;
        $this->service->update($id, $request->validated(), $usuario);

        return response()->json(['message' => 'Registro atualizado com sucesso.']);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Registro excluído com sucesso.']);
    }
}

<?php

namespace App\Http\Controllers\Relatorios\Exportacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relatorios\Exportacao\ProcessosExportacaoDocumentoRequest;
use App\Http\Requests\Relatorios\Exportacao\ProcessosExportacaoPesquisarRequest;
use App\Repositories\Logix\EmpresaRepository;
use App\Services\Reports\ProcessoExportacaoService;
use Inertia\Inertia;

class ProcessosExportacaoController extends Controller
{
    public function index(EmpresaRepository $empresaRepo)
    {
        $allowedEmpresas = ['01', '05', '20', '28', '43'];

        try {
            $empresas = $empresaRepo->all()->filter(
                fn ($e) => in_array(trim($e->ep), $allowedEmpresas)
            )->values();
        } catch (\Throwable) {
            $empresas = collect();
        }

        $documentos = config('export_documents');

        return Inertia::render('Relatorios/Exportacao/ProcessosExportacao/Index', [
            'title'      => 'Processos Exportação',
            'section'    => 'Exportação',
            'filters'    => config('reports.exportacao.processos_exportacao.filters'),
            'empresas'   => $empresas,
            'documentos' => $documentos,
        ]);
    }

    public function pesquisar(ProcessosExportacaoPesquisarRequest $request, ProcessoExportacaoService $service)
    {
        try {
            $dados = $service->search($request->validated());
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao pesquisar: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'data'  => $dados->values(),
            'total' => $dados->count(),
        ]);
    }

    public function documento(ProcessosExportacaoDocumentoRequest $request, ProcessoExportacaoService $service)
    {
        $validated = $request->validated();

        try {
            $response = $service->generateDocument(
                $validated['doc_type'],
                $validated['rows'],
                $validated['copy_type'] ?? null,
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao gerar documento: ' . $e->getMessage()], 500);
        }

        if ($response === null) {
            return response()->json(['message' => 'Nenhum dado encontrado para gerar o documento.'], 404);
        }

        return $response;
    }
}

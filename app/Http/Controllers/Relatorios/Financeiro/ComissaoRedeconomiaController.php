<?php

namespace App\Http\Controllers\Relatorios\Financeiro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relatorios\Financeiro\ComissaoRedeconomiaRequest;
use App\Services\Reports\ComissaoRedeconomiaService;
use Inertia\Inertia;

class ComissaoRedeconomiaController extends Controller
{
    public function index()
    {
        return Inertia::render('Relatorios/Financeiro/ComissaoRedeconomia/Index', [
            'title'   => 'Relatório Comissão Redeconomia',
            'filters' => config('reports.financeiro.comissao_redeconomia.filters'),
        ]);
    }

    public function gerar(ComissaoRedeconomiaRequest $request, ComissaoRedeconomiaService $service)
    {
        try {
            $response = $service->generate($request->validated());
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Erro ao gerar o relatório: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Erro ao gerar o relatório.');
        }

        if ($response === null) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Nenhum dado encontrado para os filtros informados.'], 404);
            }
            return back()->with('error', 'Nenhum dado encontrado para os filtros informados.');
        }

        return $response;
    }
}

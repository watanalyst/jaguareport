<?php

namespace App\Http\Controllers\Relatorios\Financeiro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relatorios\Financeiro\ComissaoRequest;
use App\Repositories\Logix\EmpresaRepository;
use App\Services\Reports\ComissaoService;
use Inertia\Inertia;

class ComissaoController extends Controller
{
    public function index(EmpresaRepository $empresaRepo)
    {
        try {
            $empresas = $empresaRepo->empresasPorUsuario();
        } catch (\Throwable) {
            $empresas = collect();
        }

        return Inertia::render('Relatorios/Financeiro/Comissao/Index', [
            'title'    => 'Relatório Comissão',
            'filters'  => config('reports.financeiro.contas_receber.children.comissao.filters'),
            'empresas' => $empresas,
        ]);
    }

    public function gerar(ComissaoRequest $request, ComissaoService $service)
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

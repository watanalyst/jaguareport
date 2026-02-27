<?php

namespace App\Http\Controllers\Relatorios\Financeiro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relatorios\Financeiro\FechamentoCambioRequest;
use App\Repositories\Logix\BancoRepository;
use App\Services\Reports\FechamentoCambioService;
use Inertia\Inertia;

class FechamentoCambioController extends Controller
{
    public function index(BancoRepository $bancoRepo)
    {
        try {
            $bancos = $bancoRepo->all();
        } catch (\Throwable) {
            $bancos = collect();
        }

        return Inertia::render('Relatorios/Financeiro/FechamentoCambio/Index', [
            'title'   => 'Fechamento Câmbio',
            'filters' => config('reports.financeiro.fechamento_cambio.filters'),
            'bancos'  => $bancos,
        ]);
    }

    public function gerar(FechamentoCambioRequest $request, FechamentoCambioService $service)
    {
        try {
            $format = $request->input('format', 'pdf');
            $response = $format === 'csv'
                ? $service->generateCsv($request->validated())
                : $service->generate($request->validated());
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

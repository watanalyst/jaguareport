<?php

namespace App\Http\Controllers\Relatorios\Exportacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relatorios\Exportacao\CreditNoteRequest;
use App\Repositories\Logix\EmpresaRepository;
use App\Services\Reports\CreditNoteService;
use Inertia\Inertia;

class CreditNoteController extends Controller
{
    public function index(EmpresaRepository $empresaRepo)
    {
        $allowedEmpresas = ['01', '05'];

        try {
            $empresas = $empresaRepo->all()->filter(
                fn ($e) => in_array(trim($e->ep), $allowedEmpresas)
            )->values();
        } catch (\Throwable) {
            $empresas = collect();
        }

        return Inertia::render('Relatorios/Exportacao/CreditNote/Index', [
            'title'    => 'Credit Note',
            'section'  => 'Exportação',
            'filters'  => config('reports.exportacao.financeiro_exp.children.credit_note.filters'),
            'empresas' => $empresas,
        ]);
    }

    public function gerar(CreditNoteRequest $request, CreditNoteService $service)
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

<?php

namespace App\Http\Controllers\Relatorios\Exportacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relatorios\Exportacao\ComissaoExportacaoPesquisarRequest;
use App\Services\Reports\ComissaoExportacaoService;
use Inertia\Inertia;

class ComissaoExportacaoController extends Controller
{
    public function index()
    {
        $columns = [
            ['key' => 'invoice',          'label' => 'Invoice',          'sortable' => true, 'filterable' => true],
            ['key' => 'po_cliente',       'label' => 'Ref Cliente',      'sortable' => true, 'filterable' => true],
            ['key' => 'dat_receb_adto',   'label' => 'Dat Receb Adto',  'sortable' => true, 'type' => 'date'],
            ['key' => 'dat_confirm_pgto', 'label' => 'Dat Confirm Pgto','sortable' => true, 'type' => 'date'],
            ['key' => 'nom_agente',       'label' => 'Nom Agente',      'sortable' => true, 'filterable' => true],
            ['key' => 'val_pct_comis',    'label' => 'Val Pct Comis',   'sortable' => true, 'type' => 'number'],
            ['key' => 'val_tot_item',     'label' => 'Val Tot Item',    'sortable' => true, 'type' => 'currency'],
            ['key' => 'freight',          'label' => 'Freight',         'sortable' => true, 'type' => 'currency'],
            ['key' => 'tot_comis_item',   'label' => 'Tot Comis Item',  'sortable' => true, 'type' => 'currency'],
        ];

        return Inertia::render('Relatorios/Exportacao/ComissaoExportacao/Index', [
            'title'   => 'Comissão Exportação',
            'section' => 'Exportação',
            'filters' => config('reports.exportacao.financeiro_exp.children.comissao_exportacao.filters'),
            'columns' => $columns,
        ]);
    }

    public function pesquisar(ComissaoExportacaoPesquisarRequest $request, ComissaoExportacaoService $service)
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

    public function agentes(ComissaoExportacaoService $service)
    {
        try {
            $agentes = $service->distinctAgentes();
        } catch (\Throwable) {
            return response()->json([]);
        }

        return response()->json(
            $agentes->map(fn ($a) => [
                'value' => $a->nom_agente,
                'label' => $a->nom_agente,
            ])->values()
        );
    }
}

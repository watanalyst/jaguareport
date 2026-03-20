<?php

namespace App\Http\Controllers\Relatorios\Exportacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relatorios\Exportacao\NotasFiscaisExportacaoPesquisarRequest;
use App\Repositories\Logix\EmpresaRepository;
use App\Services\Reports\NotasFiscaisExportacaoService;
use Inertia\Inertia;

class NotasFiscaisExportacaoController extends Controller
{
    public function index(EmpresaRepository $empresaRepo)
    {
        try {
            $empresas = $empresaRepo->empresasPorUsuario();
        } catch (\Throwable) {
            $empresas = collect();
        }

        $columns = [
            ['key' => 'tipo',            'label' => 'Tipo',            'sortable' => true, 'filterable' => true],
            ['key' => 'cod_empresa',     'label' => 'Emp',     'sortable' => true, 'filterable' => true, 'align' => 'center'],
            ['key' => 'num_processo',    'label' => 'Num Processo',    'sortable' => true, 'filterable' => true, 'align' => 'center'],
            ['key' => 'ano_processo',    'label' => 'Ano Processo',    'sortable' => true, 'filterable' => true, 'align' => 'center'],
            ['key' => 'embarque',        'label' => 'Embarque',        'sortable' => true, 'filterable' => true, 'align' => 'center'],
            ['key' => 'invoice',         'label' => 'Invoice',         'sortable' => true, 'filterable' => true],
            ['key' => 'nota_fiscal',     'label' => 'Nota Fiscal',     'sortable' => true, 'filterable' => true, 'align' => 'center'],
            ['key' => 'dat_hor_emissao', 'label' => 'Dat Hor Emissão','sortable' => true, 'type' => 'date'],
            ['key' => 'cod_cliente',     'label' => 'Cod Cliente',     'sortable' => true, 'filterable' => true, 'align' => 'center'],
            ['key' => 'nom_cliente',     'label' => 'Nom Cliente',     'sortable' => true, 'filterable' => true],
            ['key' => 'forma_pgto',      'label' => 'Forma Pgto',     'sortable' => true, 'filterable' => true, 'align' => 'center'],
            ['key' => 'moeda',           'label' => 'Moeda',          'sortable' => true, 'filterable' => true, 'align' => 'center'],
            ['key' => 'val_cot_moeda',   'label' => 'Val Cot Moeda',  'sortable' => true, 'type' => 'currency'],
            ['key' => 'val_moeda_ext',   'label' => 'Val Moeda Ext',  'sortable' => true, 'type' => 'currency'],
            ['key' => 'val_reais',       'label' => 'Val Reais',      'sortable' => true, 'type' => 'currency'],
            ['key' => 'banco_ext',       'label' => 'Banco Ext',      'sortable' => true, 'filterable' => true],
            ['key' => 'banco_cred',      'label' => 'Banco Cred',     'sortable' => true, 'filterable' => true],
            ['key' => 'historico',       'label' => 'Histórico',      'sortable' => true, 'filterable' => true],
        ];

        return Inertia::render('Relatorios/Exportacao/NotasFiscaisExportacao/Index', [
            'title'    => 'Notas Fiscais Exportação',
            'section'  => 'Exportação',
            'filters'  => config('reports.exportacao.financeiro_exp.children.notas_fiscais_exportacao.filters'),
            'empresas' => $empresas,
            'columns'  => $columns,
        ]);
    }

    public function pesquisar(NotasFiscaisExportacaoPesquisarRequest $request, NotasFiscaisExportacaoService $service)
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
}

<?php

namespace App\Http\Controllers\Relatorios\Exportacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relatorios\Exportacao\BaixaDisponivelPesquisarRequest;
use App\Services\Reports\BaixaDisponivelService;
use Inertia\Inertia;

class BaixaDisponivelController extends Controller
{
    public function index()
    {
        $columns = [
            ['key' => 'invoice',       'label' => 'Invoice',       'sortable' => true, 'filterable' => true],
            ['key' => 'cliente',       'label' => 'Cliente',       'sortable' => true, 'filterable' => true],
            ['key' => 'pais',          'label' => 'País',          'sortable' => true, 'filterable' => true],
            ['key' => 'moeda',         'label' => 'Moeda',         'sortable' => true, 'filterable' => true, 'align' => 'center'],
            ['key' => 'num_ope',       'label' => 'Num Ope',       'sortable' => true, 'filterable' => true],
            ['key' => 'dat_ope',       'label' => 'Dat Ope',       'sortable' => true, 'type' => 'date'],
            ['key' => 'nota_fiscal',   'label' => 'Num NF',        'sortable' => true, 'filterable' => true, 'align' => 'center'],
            ['key' => 'data_nf',       'label' => 'Data NF',       'sortable' => true, 'type' => 'date'],
            ['key' => 'cod_due',       'label' => 'DUE',           'sortable' => true, 'filterable' => true],
            ['key' => 'chave_due',     'label' => 'Chave DUE',     'sortable' => true, 'filterable' => true],
            ['key' => 'val_pago',      'label' => 'Val Pago',      'sortable' => true, 'type' => 'currency'],
            ['key' => 'val_desc_conc', 'label' => 'Val Desc Conc', 'sortable' => true, 'type' => 'currency'],
            ['key' => 'val_tarifa',    'label' => 'Val Tarifa',    'sortable' => true, 'type' => 'currency'],
            ['key' => 'form_pgto',     'label' => 'Form Pgto',     'sortable' => true, 'filterable' => true, 'align' => 'center'],
            ['key' => 'dat_conf_pgto', 'label' => 'Dat Conf Pgto', 'sortable' => true, 'type' => 'date'],
            ['key' => 'cod_banco',     'label' => 'Cod Banco',     'sortable' => true, 'filterable' => true, 'align' => 'center'],
            ['key' => 'den_banco',     'label' => 'Den Banco',     'sortable' => true, 'filterable' => true],
        ];

        return Inertia::render('Relatorios/Exportacao/BaixaDisponivel/Index', [
            'title'   => 'Baixa Disponível',
            'section' => 'Exportação',
            'filters' => config('reports.exportacao.financeiro_exp.children.baixa_disponivel.filters'),
            'columns' => $columns,
        ]);
    }

    public function pesquisar(BaixaDisponivelPesquisarRequest $request, BaixaDisponivelService $service)
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

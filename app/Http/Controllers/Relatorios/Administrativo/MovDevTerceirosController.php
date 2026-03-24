<?php

namespace App\Http\Controllers\Relatorios\Administrativo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relatorios\Administrativo\MovDevTerceirosPesquisarRequest;
use App\Repositories\Logix\EmpresaRepository;
use App\Services\Reports\MovDevTerceirosService;
use Inertia\Inertia;

class MovDevTerceirosController extends Controller
{
    public function index(EmpresaRepository $empresaRepo)
    {
        try {
            $empresas = $empresaRepo->empresasPorUsuario();
        } catch (\Throwable) {
            $empresas = collect();
        }

        $columns = [
            ['key' => 'cod_empresa',      'label' => 'Emp',              'sortable' => true, 'filterable' => true],
            ['key' => 'dat_movto',        'label' => 'Dat Movto',        'sortable' => true, 'type' => 'date'],
            ['key' => 'cod_item',         'label' => 'Cód Item',         'sortable' => true, 'filterable' => true],
            ['key' => 'saidas_estoque',   'label' => 'Saídas Estoque',   'sortable' => true, 'type' => 'currency'],
            ['key' => 'saidas_nf',        'label' => 'Saídas NF',        'sortable' => true, 'type' => 'currency'],
            ['key' => 'devol_estoque',    'label' => 'Devol Estoque',    'sortable' => true, 'type' => 'currency'],
            ['key' => 'devol_nf',         'label' => 'Devol NF',         'sortable' => true, 'type' => 'currency'],
            ['key' => 'refat_estoque',    'label' => 'Refat Estoque',    'sortable' => true, 'type' => 'currency'],
            ['key' => 'refat_nf',         'label' => 'Refat NF',         'sortable' => true, 'type' => 'currency'],
            ['key' => 'baixa_estoque',    'label' => 'Baixa Estoque',    'sortable' => true, 'type' => 'currency'],
            ['key' => 'baixa_nf',         'label' => 'Baixa NF',         'sortable' => true, 'type' => 'currency'],
            ['key' => 'rem_terc_estoque', 'label' => 'Rem Terc Estoque', 'sortable' => true, 'type' => 'currency'],
            ['key' => 'rem_terc_nf',      'label' => 'Rem Terc NF',      'sortable' => true, 'type' => 'currency'],
            ['key' => 'ret_terc_estoque', 'label' => 'Ret Terc Estoque', 'sortable' => true, 'type' => 'currency'],
            ['key' => 'ret_terc_nf',      'label' => 'Ret Terc NF',      'sortable' => true, 'type' => 'currency'],
        ];

        return Inertia::render('Relatorios/Administrativo/MovDevTerceiros/Index', [
            'title'    => 'Mov. Dev. e Terceiros',
            'section'  => 'Administrativo',
            'filters'  => config('reports.administrativo.fomento.children.mov_dev_terceiros.filters'),
            'empresas' => $empresas,
            'columns'  => $columns,
        ]);
    }

    public function pesquisar(MovDevTerceirosPesquisarRequest $request, MovDevTerceirosService $service)
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

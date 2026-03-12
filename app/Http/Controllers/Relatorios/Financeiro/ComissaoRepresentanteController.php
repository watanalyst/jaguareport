<?php

namespace App\Http\Controllers\Relatorios\Financeiro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relatorios\Financeiro\ComissaoRepresentantePesquisarRequest;
use App\Services\Reports\ComissaoRepresentanteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ComissaoRepresentanteController extends Controller
{
    public function index()
    {
        try {
            $empresas = DB::connection('logix')
                ->table('RELATORIOS.SC_COMISSAO1')
                ->selectRaw('DISTINCT EP, EMPRESA')
                ->where('DAT_EMIS', '>=', '2025-01-01')
                ->orderBy('EP')
                ->get()
                ->map(fn ($e) => (object) ['ep' => trim($e->ep), 'den_reduz' => trim($e->empresa)]);
        } catch (\Throwable) {
            $empresas = collect();
        }

        $columns = [
            ['key' => 'emp',          'label' => 'Emp',           'sortable' => true, 'filterable' => true, 'align' => 'center'],
            ['key' => 'cod_repres',   'label' => 'Cód Repres',   'sortable' => true, 'filterable' => true, 'align' => 'center'],
            ['key' => 'nome_repres',  'label' => 'Nome Representante', 'sortable' => true, 'filterable' => true],
            ['key' => 'mes_comissao', 'label' => 'Mês Comissão',  'sortable' => true, 'type' => 'date'],
            ['key' => 'val_comissao', 'label' => 'Valor Comissão', 'sortable' => true, 'type' => 'currency'],
            ['key' => 'status_aprov', 'label' => 'Status',        'sortable' => true, 'filterable' => true, 'align' => 'center', 'filterMap' => ['S' => 'Aprovado', 'N' => 'Pendente']],
        ];

        return Inertia::render('Relatorios/Financeiro/ComissaoRepresentante/Index', [
            'title'    => 'Comissão Representante',
            'section'  => 'Financeiro',
            'filters'  => config('reports.financeiro.comissao_representante.filters'),
            'empresas' => $empresas,
            'columns'  => $columns,
        ]);
    }

    public function pesquisar(ComissaoRepresentantePesquisarRequest $request, ComissaoRepresentanteService $service)
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

    public function aprovar(Request $request, ComissaoRepresentanteService $service)
    {
        $request->validate([
            'registros'              => ['required', 'array', 'min:1'],
            'registros.*.emp'        => ['required', 'string', 'size:2'],
            'registros.*.cod_repres' => ['required', 'integer'],
            'registros.*.mes_comissao' => ['required', 'date'],
            'registros.*.nome_repres'  => ['nullable', 'string'],
            'registros.*.val_comissao' => ['required', 'numeric'],
        ]);

        $usuario = $request->user()?->sc_user ?? $request->user()?->name ?? 'SISTEMA';

        try {
            $count = $service->aprovar($request->input('registros'), $usuario);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao aprovar: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => "{$count} registro(s) aprovado(s) com sucesso.",
        ]);
    }

    public function desaprovar(Request $request, ComissaoRepresentanteService $service)
    {
        $request->validate([
            'registros'                => ['required', 'array', 'min:1'],
            'registros.*.emp'          => ['required', 'string', 'size:2'],
            'registros.*.cod_repres'   => ['required', 'integer'],
            'registros.*.mes_comissao' => ['required', 'date'],
        ]);

        try {
            $count = $service->desaprovar($request->input('registros'));
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao desaprovar: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => "{$count} registro(s) desaprovado(s).",
        ]);
    }
}

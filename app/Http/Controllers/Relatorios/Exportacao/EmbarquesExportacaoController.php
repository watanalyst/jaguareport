<?php

namespace App\Http\Controllers\Relatorios\Exportacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relatorios\Exportacao\EmbarquesExportacaoPesquisarRequest;
use App\Repositories\Logix\EmpresaRepository;
use App\Services\Reports\EmbarqueExportacaoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmbarquesExportacaoController extends Controller
{
    private const ALLOWED_EMPRESAS = ['01', '05', '20', '28', '43'];

    public function index(EmpresaRepository $empresaRepo)
    {
        try {
            $empresas = $empresaRepo->all()->filter(
                fn ($e) => in_array(trim($e->ep), self::ALLOWED_EMPRESAS)
            )->values();
        } catch (\Throwable) {
            $empresas = collect();
        }

        $columns = [
            ['key' => 'empresa',        'label' => 'Emp',            'sortable' => true, 'filterable' => true, 'align' => 'center'],
            ['key' => 'nom_agente',     'label' => 'Agente',         'sortable' => true, 'filterable' => true],
            ['key' => 'buyer',          'label' => 'Buyer',          'sortable' => true, 'filterable' => true],
            ['key' => 'nom_consig_red', 'label' => 'Nom Consig Red',  'sortable' => true, 'filterable' => true],
            ['key' => 'processos',      'label' => 'Processos',      'sortable' => true, 'filterable' => true],
            ['key' => 'po_cliente',     'label' => 'PO Cliente',     'sortable' => true, 'filterable' => true],
            ['key' => 'num_pedido',     'label' => 'Pedido',         'sortable' => true, 'filterable' => true],
            ['key' => 'qtd_total',      'label' => 'Qtd Total',      'sortable' => true, 'type' => 'number'],
            ['key' => 'cod_item',       'label' => 'Cód Item',       'sortable' => true, 'filterable' => true],
            ['key' => 'item_reduz',     'label' => 'Item Reduz',     'sortable' => true, 'filterable' => true],
            ['key' => 'import_permit',  'label' => 'Import Permit',  'sortable' => true, 'filterable' => true],
            ['key' => 'val_receb_adto', 'label' => 'Val Receb Adto', 'sortable' => true, 'type' => 'currency'],
            ['key' => 'pais_destino',   'label' => 'País Destino',   'sortable' => true, 'filterable' => true],
            ['key' => 'porto_destino',  'label' => 'Porto Destino',  'sortable' => true, 'filterable' => true],
            ['key' => 'booking',        'label' => 'Booking',        'sortable' => true, 'filterable' => true],
            ['key' => 'nom_armador',    'label' => 'Nom Armador',    'sortable' => true, 'filterable' => true],
            ['key' => 'navio',          'label' => 'Navio',          'sortable' => true, 'filterable' => true],
            ['key' => 'etd',            'label' => 'ETD',            'sortable' => true, 'type' => 'date'],
            ['key' => 'eta',            'label' => 'ETA',            'sortable' => true, 'type' => 'date'],
            ['key' => 'local_embarque', 'label' => 'Local Embarque', 'sortable' => true, 'filterable' => true],
            ['key' => 'dados_transp',   'label' => 'Dados Transp',   'sortable' => true, 'filterable' => true],
            ['key' => 'sit',            'label' => 'Sit',            'sortable' => true, 'filterable' => true],
            ['key' => 'num_vdj',        'label' => 'Num VDJ',        'sortable' => true, 'filterable' => true],
            ['key' => 'prev_vdj',       'label' => 'Prev VDJ',       'sortable' => true, 'type' => 'date'],
            ['key' => 'obs_vdj',        'label' => 'Obs VDJ',        'sortable' => true, 'filterable' => true],
        ];

        return Inertia::render('Relatorios/Exportacao/EmbarquesExportacao/Index', [
            'title'    => 'Embarques Exportação',
            'section'  => 'Exportação',
            'filters'  => config('reports.exportacao.embarques_exportacao.filters'),
            'empresas' => $empresas,
            'columns'  => $columns,
        ]);
    }

    public function pesquisar(EmbarquesExportacaoPesquisarRequest $request, EmbarqueExportacaoService $service)
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

    /**
     * Retorna itens distintos para o autocomplete de COD_ITEM.
     */
    public function items(Request $request, EmbarqueExportacaoService $service)
    {
        $empresa = $request->input('empresa');

        if (! $empresa) {
            return response()->json([]);
        }

        try {
            $items = $service->distinctItems($empresa);
        } catch (\Throwable) {
            return response()->json([]);
        }

        return response()->json(
            $items->map(fn ($i) => [
                'value' => $i->cod_item,
                'label' => "{$i->cod_item} - {$i->item_reduz}",
            ])->values()
        );
    }
}

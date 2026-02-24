<?php

namespace App\Services\Reports;

use App\Repositories\Logix\ComissaoRedeconomiaRepository;
use App\Services\Exporters\PdfExporter;
use Carbon\Carbon;

class ComissaoRedeconomiaService
{
    public function __construct(
        private ComissaoRedeconomiaRepository $repository,
        private PdfExporter $exporter,
    ) {}

    public function generate(array $params)
    {
        set_time_limit(600);
        ini_set('memory_limit', '-1');

        $dados = $this->repository->fetch($params);

        if ($dados->isEmpty()) {
            return null;
        }

        $dadosPorEmpresa = $dados->groupBy('ep');

        $inicio = Carbon::parse($params['data_ini'])->format('d_m_Y');
        $fim    = Carbon::parse($params['data_fim'])->format('d_m_Y');
        $nomeArquivo = "Comissao_Redeconomia_{$inicio}_a_{$fim}.pdf";

        return $this->exporter->download('reports.pdf.comissao-redeconomia', [
            'dadosPorEmpresa' => $dadosPorEmpresa,
            'filtros' => $params,
        ], $nomeArquivo);
    }
}

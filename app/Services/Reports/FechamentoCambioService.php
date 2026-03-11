<?php

namespace App\Services\Reports;

use App\Repositories\Logix\FechamentoCambioRepository;
use App\Services\Exporters\CsvExporter;
use App\Services\Exporters\PdfExporter;

class FechamentoCambioService
{
    public function __construct(
        private FechamentoCambioRepository $repository,
        private PdfExporter $pdfExporter,
        private CsvExporter $csvExporter,
    ) {}

    public function generate(array $params)
    {
        set_time_limit(600);
        ini_set('memory_limit', '-1');

        $dados = $this->repository->fetch($params);

        if ($dados->isEmpty()) {
            return null;
        }

        $grupos = $dados->groupBy('num_seq_cambio');

        $sequencias = [];

        foreach ($grupos as $numSeq => $registros) {
            $registros = $registros->sortBy('dat_cred')->values();
            $primeiro = $registros[0];

            $totValInvoice  = 0;
            $totValComDesc  = 0;
            $totComissao    = 0;

            foreach ($registros as $row) {
                $comissao = ($row->forma_comis === 'G') ? ($row->comissao ?? 0) : 0;
                $totValInvoice  += $row->val_invoice ?? 0;
                $totValComDesc  += $row->val_com_desc ?? 0;
                $totComissao    += $comissao;
            }

            $tipoCalc = trim($primeiro->tipo_calc ?? '');

            if ($tipoCalc === 'Líquido') {
                $totalUs = $totValComDesc - $totComissao;
            } else {
                $totalUs = $totValInvoice - $totComissao;
            }

            $valCotacao = $primeiro->val_cotacao ?? 0;
            $totalTaxa  = $totalUs * $valCotacao;

            $sequencias[] = (object) [
                'num_seq'         => $numSeq,
                'registros'       => $registros,
                'primeiro'        => $primeiro,
                'tot_val_invoice' => $totValInvoice,
                'tot_val_com_desc'=> $totValComDesc,
                'tot_comissao'    => $totComissao,
                'total_us'        => $totalUs,
                'val_cotacao'     => $valCotacao,
                'total_taxa'      => $totalTaxa,
                'tipo_calc'       => $tipoCalc,
            ];
        }

        $pdfContent = $this->pdfExporter->generate('reports.pdf.financeiro.fechamento-cambio', [
            'sequencias' => $sequencias,
            'filtros'    => $params,
        ]);

        $dataCambio = str_replace('-', '', $params['dat_cambio']);
        $filename = "Fechamento_Cambio_{$dataCambio}.pdf";

        return response()->streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function generateCsv(array $params)
    {
        $dados = $this->repository->fetch($params);

        if ($dados->isEmpty()) {
            return null;
        }

        $grupos = $dados->groupBy('num_seq_cambio');
        $dataCambio = str_replace('-', '', $params['dat_cambio']);
        $dataCambioFmt = \Carbon\Carbon::parse($params['dat_cambio'])->format('d/m/Y');

        $filename = "Fechamento_Cambio_{$dataCambio}.csv";

        return response()->streamDownload(function () use ($grupos, $dataCambioFmt) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 para Excel reconhecer acentos
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            foreach ($grupos as $numSeq => $registros) {
                $registros = $registros->sortBy('dat_cred')->values();
                $p = $registros[0];

                // Cabeçalho da sequência
                fputcsv($handle, ['FECHAMENTO CAMBIO'], ';');
                fputcsv($handle, ['Banco: ' . ($p->bank ?? '')], ';');
                fputcsv($handle, ['Agencia: ' . ($p->agencia ?? '')], ';');
                fputcsv($handle, ['Conta: ' . ($p->conta ?? '')], ';');
                fputcsv($handle, ['Empresa: ' . ($p->den_empresa ?? '')], ';');
                fputcsv($handle, ['CNPJ: ' . ($p->cnpj ?? '')], ';');
                fputcsv($handle, ['Data Cambio: ' . $dataCambioFmt], ';');
                fputcsv($handle, ['Seq.: ' . $numSeq], ';');

                // Cabeçalho da tabela
                fputcsv($handle, [
                    'Num OPE', 'Inc.Cred.', 'NF', 'Data NF', 'DUE', 'Chave Acesso',
                    'Importador', 'Ordenante', 'Trader', 'Pais', 'Moeda',
                    'Vl.Invoice', 'Vl.C/ Desc.', 'Comissao', 'Data Cambio',
                    'Invoice', 'Pgto', 'Fech',
                ], ';');

                $totValInvoice = 0;
                $totValComDesc = 0;
                $totComissao   = 0;

                foreach ($registros as $row) {
                    $comissao = ($row->forma_comis === 'G') ? ($row->comissao ?? 0) : 0;
                    $totValInvoice += $row->val_invoice ?? 0;
                    $totValComDesc += $row->val_com_desc ?? 0;
                    $totComissao   += $comissao;

                    $datCred      = !empty($row->dat_cred)   ? \Carbon\Carbon::parse($row->dat_cred)->format('d/m/Y')   : '';
                    $datNf        = !empty($row->dat_nf)     ? \Carbon\Carbon::parse($row->dat_nf)->format('d/m/Y')     : '';
                    $datCambioRow = !empty($row->dat_cambio) ? \Carbon\Carbon::parse($row->dat_cambio)->format('d/m/Y') : '';

                    fputcsv($handle, [
                        $row->num_ope,
                        $datCred,
                        $row->nota_fiscal,
                        $datNf,
                        $row->due,
                        $row->chave_due,
                        $row->importador,
                        $row->ordenante,
                        $row->trader,
                        $row->pais,
                        $row->moeda,
                        number_format($row->val_invoice ?? 0, 2, ',', '.'),
                        number_format($row->val_com_desc ?? 0, 2, ',', '.'),
                        ($row->moeda ?? '') . ' ' . number_format($comissao, 2, ',', '.'),
                        $datCambioRow,
                        $row->invoice,
                        $row->cod_forma_pgto,
                        $row->fech,
                    ], ';');
                }

                // Observação + totais
                $tipoCalc = trim($p->tipo_calc ?? '');
                $totalUs  = ($tipoCalc === 'Líquido')
                    ? $totValComDesc - $totComissao
                    : $totValInvoice - $totComissao;
                $valCotacao = $p->val_cotacao ?? 0;
                $totalTaxa  = $totalUs * $valCotacao;

                fputcsv($handle, ['*Obs.: Informa se o valor da ordem e diferente a um pago T (Total), P (Parcial) ou A (Antecipado).'], ';');
                fputcsv($handle, ['Total Vl. Invoice.: ' . number_format($totValInvoice, 2, ',', '.')], ';');
                fputcsv($handle, ['Total Vl. C/ Desc.: ' . number_format($totValComDesc, 2, ',', '.')], ';');
                fputcsv($handle, ['Total Comissao: ' . ($p->moeda ?? '') . ' ' . number_format($totComissao, 2, ',', '.')], ';');
                fputcsv($handle, ['Sem mais para o momento, antecipamos nossos agradecimentos.'], ';');
                fputcsv($handle, ['>R$ ' . number_format($valCotacao, 4, ',', '.')], ';');
                fputcsv($handle, ['>Total: ' . number_format($totalTaxa, 2, ',', '.')], ';');

                if ($tipoCalc === 'Líquido') {
                    fputcsv($handle, ['Jaguafrangos Ind. Com. Alim. Ltda.'], ';');
                } else {
                    fputcsv($handle, [$p->den_empresa ?? ''], ';');
                }

                // Linha em branco separando sequências
                fputcsv($handle, [''], ';');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

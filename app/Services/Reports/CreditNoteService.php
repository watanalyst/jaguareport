<?php

namespace App\Services\Reports;

use App\Repositories\Logix\CreditNoteRepository;
use App\Services\Exporters\PdfExporter;

class CreditNoteService
{
    public function __construct(
        private CreditNoteRepository $repository,
        private PdfExporter $pdfExporter,
    ) {}

    public function generate(array $params)
    {
        $header = $this->repository->fetchHeader(
            $params['cod_empresa'],
            $params['num_nc'],
            $params['ano_nc'],
        );

        if (! $header) {
            return null;
        }

        $items = $this->repository->fetchItems(
            $params['cod_empresa'],
            $params['num_nc'],
            $params['ano_nc'],
        );

        $branding = config(
            'company_branding.' . trim($header->cod_empresa),
            config('company_branding.default')
        );

        $pdfContent = $this->pdfExporter->generate(
            'reports.pdf.exportacao.credit-note',
            [
                'header'      => $header,
                'items'       => $items,
                'dados_banco' => $params['dados_banco'],
                'branding'    => $branding,
            ],
            'portrait',
        );

        $filename = "Credit_Note_{$header->num_nc}_{$header->ano_nc}.pdf";

        return response()->streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, $filename, ['Content-Type' => 'application/pdf']);
    }
}

<?php

namespace App\Services\Reports;

use App\Repositories\Logix\DebitNoteRepository;
use App\Services\Exporters\PdfExporter;

class DebitNoteService
{
    public function __construct(
        private DebitNoteRepository $repository,
        private PdfExporter $pdfExporter,
    ) {}

    public function generate(array $params)
    {
        $header = $this->repository->fetchHeader(
            $params['cod_empresa'],
            $params['num_nd'],
            $params['ano_nd'],
        );

        if (! $header) {
            return null;
        }

        $items = $this->repository->fetchItems(
            $params['cod_empresa'],
            $params['num_nd'],
            $params['ano_nd'],
        );

        $branding = config(
            'company_branding.' . trim($header->cod_empresa),
            config('company_branding.default')
        );

        $pdfContent = $this->pdfExporter->generate(
            'reports.pdf.exportacao.debit-note',
            [
                'header'   => $header,
                'items'    => $items,
                'branding' => $branding,
            ],
            'portrait',
        );

        $filename = "Debit_Note_{$header->num_nd}_{$header->ano_nd}.pdf";

        return response()->streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, $filename, ['Content-Type' => 'application/pdf']);
    }
}

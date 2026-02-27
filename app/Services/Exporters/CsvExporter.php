<?php

namespace App\Services\Exporters;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter
{
    public function generate(array $headers, Collection $rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 para Excel reconhecer acentos
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, $headers, ';');

            foreach ($rows as $row) {
                fputcsv($handle, (array) $row, ';');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

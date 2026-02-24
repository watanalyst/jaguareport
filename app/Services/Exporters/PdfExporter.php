<?php

namespace App\Services\Exporters;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfExporter
{
    public function generate(string $view, array $data, string $orientation = 'landscape'): string
    {
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', $orientation);

        return $pdf->output();
    }

    public function download(string $view, array $data, string $filename, string $orientation = 'landscape'): Response
    {
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', $orientation);

        return $pdf->download($filename);
    }
}

<?php

namespace App\Services\Reports;

use App\Repositories\Logix\ComissaoRepository;
use App\Services\Exporters\PdfExporter;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class ComissaoService
{
    public function __construct(
        private ComissaoRepository $repository,
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

        $pdfs = $this->gerarRelatoriosIndividualmente($dados, $params);

        if (count($pdfs) === 1) {
            $nomeArquivo = array_key_first($pdfs);
            $conteudo = $pdfs[$nomeArquivo];

            return response()->streamDownload(function () use ($conteudo) {
                echo $conteudo;
            }, $nomeArquivo, ['Content-Type' => 'application/pdf']);
        }

        return $this->baixarComoZip($pdfs);
    }

    private function gerarRelatoriosIndividualmente($dados, array $filtros): array
    {
        $grupos = $dados->groupBy(fn($item) => strval(trim($item->cod_repres)) . '|' . strval(trim($item->ep)));

        $pdfs = [];

        foreach ($grupos as $chave => $registros) {
            [$codRepres, $ep] = explode('|', $chave);

            try {
                $registrosOrdenados = $registros->sortBy('titulo')->values();
                $nomeRepresentante = $this->sanitizeNome(trim($registros[0]->nome_repres ?? ''));
                $nomePdf = "EMP{$ep}-{$codRepres}-{$nomeRepresentante}.pdf";

                $pdfs[$nomePdf] = $this->exporter->generate('reports.pdf.financeiro.comissao', [
                    'dados'   => $registrosOrdenados,
                    'filtros' => $filtros,
                ]);
            } catch (\Throwable $e) {
                Log::error("Erro ao gerar PDF do grupo {$chave}", ['erro' => $e->getMessage()]);
            }
        }

        return $pdfs;
    }

    private function baixarComoZip(array $pdfs)
    {
        $zipFileName = 'comissoes_' . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path("app/public/{$zipFileName}");

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        foreach ($pdfs as $nome => $conteudo) {
            $zip->addFromString($nome, $conteudo);
        }

        $zip->close();

        if (!file_exists($zipPath)) {
            return null;
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    private function sanitizeNome(string $nome): string
    {
        $nome = preg_replace('/[\/\\\\:*?"<>|]/', '', $nome);
        return str_replace(' ', '-', $nome);
    }
}

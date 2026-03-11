<?php

namespace App\Services\Reports;

use App\Repositories\Logix\ProcessoExportacaoRepository;
use App\Services\Exporters\PdfExporter;
use Illuminate\Support\Collection;
use ZipArchive;

class ProcessoExportacaoService
{
    /** Documentos cujo escopo é por processo (empresa+processo+ano), sem embarque. */
    private const PROCESS_LEVEL_DOCS = ['PF'];

    public function __construct(
        private ProcessoExportacaoRepository $repository,
        private PdfExporter $pdfExporter,
    ) {}

    public function search(array $params): Collection
    {
        return $this->repository->search($params);
    }

    /**
     * Gera PDF de um documento para uma ou mais linhas.
     */
    public function generateDocument(string $docType, array $rows, ?string $copyType = null)
    {
        set_time_limit(600);
        ini_set('memory_limit', '-1');

        $docConfig = config("export_documents.{$docType}");

        if (! $docConfig) {
            return null;
        }

        // Deduplica linhas para documentos por processo (ignora embarque)
        $uniqueKeys = $this->deduplicateRows($docType, $rows);

        if (count($uniqueKeys) === 1) {
            return $this->buildSinglePdf($docType, $docConfig, $uniqueKeys[0], $copyType);
        }

        return $this->buildZipPdf($docType, $docConfig, $uniqueKeys, $copyType);
    }

    /**
     * Remove duplicatas de (empresa, processo, ano) para documentos por processo.
     */
    private function deduplicateRows(string $docType, array $rows): array
    {
        if (! in_array($docType, self::PROCESS_LEVEL_DOCS)) {
            return $rows;
        }

        $seen = [];
        $unique = [];

        foreach ($rows as $row) {
            $key = "{$row['empresa']}_{$row['processo']}_{$row['ano']}";

            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $row;
            }
        }

        return $unique;
    }

    /**
     * Busca dados conforme o tipo de documento.
     */
    private function fetchDataForDocument(string $docType, array $keys, ?string $copyType): ?array
    {
        switch ($docType) {
            case 'PF':
                $header = $this->repository->fetchProformaHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano']
                );

                if (! $header) {
                    return null;
                }

                $items = $this->repository->fetchProformaItems(
                    $keys['empresa'], $keys['processo'], $keys['ano']
                );

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'   => $header,
                    'items'    => $items,
                    'branding' => $branding,
                    'copyType' => $copyType,
                ];

            case 'BL':
                $header = $this->repository->fetchBLHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $totComissao = $this->repository->fetchBLTotComissao(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );
                $itemAC = $this->repository->fetchBLItemAC(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );
                $cubagem = $this->repository->fetchBLCubagem(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );
                $items = $this->repository->fetchBLItems(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'       => $header,
                    'items'        => $items,
                    'cubagem'      => $cubagem,
                    'totComissao'  => $totComissao,
                    'itemAC'       => $itemAC,
                    'branding'     => $branding,
                    'copyType'     => $copyType,
                ];

            case 'CKL':
                $items = $this->repository->fetchCKLData(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if ($items->isEmpty()) {
                    return null;
                }

                $header = $items->first();

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'   => $header,
                    'items'    => $items,
                    'branding' => $branding,
                ];

            case 'IN':
                $header = $this->repository->fetchInvoiceHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $ncm = $this->repository->fetchInvoiceNCM(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $items = $this->repository->fetchInvoiceItems(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'   => $header,
                    'items'    => $items,
                    'ncm'      => $ncm,
                    'branding' => $branding,
                    'copyType' => $copyType,
                ];

            case 'INF':
                $header = $this->repository->fetchInvoiceFreteHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $ncm = $this->repository->fetchInvoiceNCM(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $items = $this->repository->fetchInvoiceFreteItems(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'   => $header,
                    'items'    => $items,
                    'ncm'      => $ncm,
                    'branding' => $branding,
                    'copyType' => $copyType,
                ];

            case 'PL':
                $header = $this->repository->fetchPLHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                // Determine variant for item query
                $paisDestino = strtoupper(trim($header->pais_destino ?? ''));
                $spanishCountries = ['ARGENTINA', 'CHILE', 'URUGUAY', 'COLOMBIA', 'ESPANHA'];
                $isSpanish = in_array($paisDestino, $spanishCountries);

                $summaryItems = $isSpanish
                    ? $this->repository->fetchPLSummaryItemsSimple(
                        $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                    )
                    : $this->repository->fetchPLSummaryItems(
                        $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                    );

                $ncm = $this->repository->fetchInvoiceNCM(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                // Packing detail (production dates per item)
                $numPedido = trim($header->num_pedido ?? '');
                $packingDetail = [];

                if ($numPedido) {
                    $distinctItems = $this->repository->fetchPLDistinctItems(
                        $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque'], $numPedido
                    );

                    foreach ($distinctItems as $item) {
                        $codItem = trim($item->cod_item);
                        $details = $this->repository->fetchPLPackingDetail(
                            $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque'],
                            $numPedido, $codItem
                        );

                        // Group by production_date|expiry_date
                        $grouped = [];
                        foreach ($details as $d) {
                            $key = ($d->production_date ?? '') . '|' . ($d->expiry_date ?? '');
                            if (! isset($grouped[$key])) {
                                $grouped[$key] = (object) [
                                    'production_date' => $d->production_date,
                                    'expiry_date'     => $d->expiry_date,
                                    'cartons'         => 0,
                                    'net_weight'      => 0,
                                    'gross_weight'    => 0,
                                    'lots'            => $d->lots ?? null,
                                ];
                            }
                            $grouped[$key]->cartons      += (float) ($d->cartons ?? 0);
                            $grouped[$key]->net_weight   += (float) ($d->net_weight ?? 0);
                            $grouped[$key]->gross_weight += (float) ($d->gross_weight ?? 0);
                        }

                        if (! empty($grouped)) {
                            $packingDetail[$codItem] = array_values($grouped);
                        }
                    }
                }

                return [
                    'header'        => $header,
                    'summaryItems'  => $summaryItems,
                    'packingDetail' => $packingDetail,
                    'ncm'           => $ncm,
                    'branding'      => $branding,
                    'copyType'      => $copyType,
                ];

            case 'FIN':
                $header = $this->repository->fetchInvoiceFreteHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $ncm = $this->repository->fetchInvoiceNCM(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $items = $this->repository->fetchInvoiceFreteItems(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'   => $header,
                    'items'    => $items,
                    'ncm'      => $ncm,
                    'branding' => $branding,
                    'copyType' => $copyType,
                ];

            case 'DC':
                $header = $this->repository->fetchDCHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $items = $this->repository->fetchDCItems(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'   => $header,
                    'items'    => $items,
                    'branding' => $branding,
                    'copyType' => $copyType,
                ];

            case 'CR':
                $header = $this->repository->fetchDCHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $items = $this->repository->fetchDCItems(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $ncm = $this->repository->fetchInvoiceNCM(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'   => $header,
                    'items'    => $items,
                    'ncm'      => $ncm,
                    'branding' => $branding,
                    'copyType' => $copyType,
                ];

            case 'SC':
            case 'DPM':
                $header = $this->repository->fetchDCHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $items = $this->repository->fetchDCItems(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'   => $header,
                    'items'    => $items,
                    'branding' => $branding,
                    'copyType' => $copyType,
                ];

            case 'ISF':
                $header = $this->repository->fetchISFHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $ncm = $this->repository->fetchInvoiceNCM(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'   => $header,
                    'ncm'      => $ncm,
                    'branding' => $branding,
                    'copyType' => $copyType,
                ];

            case 'DEC':
                $header = $this->repository->fetchDECHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'   => $header,
                    'branding' => $branding,
                ];

            case 'HFC':
                $header = $this->repository->fetchHFCHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $items = $this->repository->fetchDCItems(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $ncm = $this->repository->fetchInvoiceNCM(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                // Production dates — MIMS only (ID_INTEMATEEMBA), same as Scriptcase
                $numPedido = trim($header->num_pedido ?? '');
                $productionDateRange = $numPedido
                    ? $this->repository->fetchHFCProductionDateRange($keys['empresa'], $numPedido)
                    : null;

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'              => $header,
                    'items'               => $items,
                    'ncm'                 => $ncm,
                    'productionDateRange' => $productionDateRange,
                    'branding'            => $branding,
                    'copyType'            => $copyType,
                ];

            case 'FC':
            case 'NDFC':
            case 'CONG':
            case 'QC':
            case 'MFC':
            case 'AFC':
                $header = $this->repository->fetchHFCHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $items = $this->repository->fetchDCItems(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $ncm = $this->repository->fetchInvoiceNCM(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                // Production dates — MIMS only (ID_INTEMATEEMBA), same as HFC
                $numPedido = trim($header->num_pedido ?? '');
                $productionDateRange = $numPedido
                    ? $this->repository->fetchHFCProductionDateRange($keys['empresa'], $numPedido)
                    : null;

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'              => $header,
                    'items'               => $items,
                    'ncm'                 => $ncm,
                    'productionDateRange' => $productionDateRange,
                    'branding'            => $branding,
                    'copyType'            => $copyType,
                ];

            case 'WC':
                $header = $this->repository->fetchWCHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $items = $this->repository->fetchInvoiceItems(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $ncm = $this->repository->fetchInvoiceNCM(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                // Production dates — MIMS (same as HFC)
                $numPedido = trim($header->num_pedido ?? '');
                $productionDateRange = $numPedido
                    ? $this->repository->fetchHFCProductionDateRange($keys['empresa'], $numPedido)
                    : null;

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'              => $header,
                    'items'               => $items,
                    'ncm'                 => $ncm,
                    'productionDateRange' => $productionDateRange,
                    'branding'            => $branding,
                    'copyType'            => $copyType,
                ];

            case 'EALNC':
                $header = $this->repository->fetchWCHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $items = $this->repository->fetchInvoiceItems(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                $ncm = $this->repository->fetchInvoiceNCM(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                // Packing detail per item (same logic as PL)
                $numPedido = trim($header->num_pedido ?? '');
                $packingDetail = [];

                if ($numPedido) {
                    $distinctItems = $this->repository->fetchPLDistinctItems(
                        $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque'], $numPedido
                    );

                    foreach ($distinctItems as $item) {
                        $codItem = trim($item->cod_item);
                        $details = $this->repository->fetchPLPackingDetail(
                            $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque'],
                            $numPedido, $codItem
                        );

                        // Group by production_date|expiry_date
                        $grouped = [];
                        foreach ($details as $d) {
                            $key = ($d->production_date ?? '') . '|' . ($d->expiry_date ?? '');
                            if (! isset($grouped[$key])) {
                                $grouped[$key] = (object) [
                                    'production_date' => $d->production_date,
                                    'expiry_date'     => $d->expiry_date,
                                    'cartons'         => 0,
                                    'net_weight'      => 0,
                                    'gross_weight'    => 0,
                                    'lots'            => $d->lots ?? null,
                                ];
                            }
                            $grouped[$key]->cartons      += (float) ($d->cartons ?? 0);
                            $grouped[$key]->net_weight   += (float) ($d->net_weight ?? 0);
                            $grouped[$key]->gross_weight += (float) ($d->gross_weight ?? 0);
                        }

                        if (! empty($grouped)) {
                            $packingDetail[$codItem] = array_values($grouped);
                        }
                    }
                }

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'        => $header,
                    'items'         => $items,
                    'ncm'           => $ncm,
                    'packingDetail' => $packingDetail,
                    'branding'      => $branding,
                    'copyType'      => $copyType,
                ];

            case 'VGM':
                $header = $this->repository->fetchVGMHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'   => $header,
                    'branding' => $branding,
                    'copyType' => $copyType,
                ];

            case 'PRO':
                $header = $this->repository->fetchPROHeader(
                    $keys['empresa'], $keys['processo'], $keys['ano'], $keys['embarque']
                );

                if (! $header) {
                    return null;
                }

                $branding = config(
                    'company_branding.' . trim($header->cod_empresa),
                    config('company_branding.default')
                );

                return [
                    'header'   => $header,
                    'branding' => $branding,
                ];

            default:
                $dados = $this->repository->fetchDocumentData(
                    $keys['empresa'], $keys['processo'], $keys['embarque'], $keys['ano']
                );

                if ($dados->isEmpty()) {
                    return null;
                }

                return ['dados' => $dados, 'copyType' => $copyType];
        }
    }

    private function buildSinglePdf(string $docType, array $docConfig, array $keys, ?string $copyType)
    {
        $data = $this->fetchDataForDocument($docType, $keys, $copyType);

        if ($data === null) {
            return null;
        }

        $pdfContent = $this->pdfExporter->generate(
            $docConfig['view'],
            $data,
            $docConfig['orientation'],
        );

        $filename = $this->buildFilename($docType, $docConfig, $keys, $copyType);

        return response()->streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    private function buildZipPdf(string $docType, array $docConfig, array $rows, ?string $copyType)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'exp_');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($rows as $keys) {
            $data = $this->fetchDataForDocument($docType, $keys, $copyType);

            if ($data === null) {
                continue;
            }

            $pdfContent = $this->pdfExporter->generate(
                $docConfig['view'],
                $data,
                $docConfig['orientation'],
            );

            $zip->addFromString(
                $this->buildFilename($docType, $docConfig, $keys, $copyType),
                $pdfContent,
            );
        }

        $zip->close();

        $filename = "{$docConfig['label']}_{$docType}_" . now()->format('Ymd_His') . '.zip';

        return response()->streamDownload(function () use ($tempFile) {
            readfile($tempFile);
            @unlink($tempFile);
        }, $filename, ['Content-Type' => 'application/zip']);
    }

    private function buildFilename(string $docType, array $docConfig, array $keys, ?string $copyType): string
    {
        $parts = [
            str_replace(' ', '_', $docConfig['label']),
            $keys['empresa'],
            $keys['processo'],
            $keys['ano'],
        ];

        if (! in_array($docType, self::PROCESS_LEVEL_DOCS) && ! empty($keys['embarque'])) {
            $parts[] = $keys['embarque'];
        }

        if ($copyType) {
            $parts[] = $copyType === 'original' ? 'Original' : 'Copia';
        }

        return implode('_', $parts) . '.pdf';
    }
}

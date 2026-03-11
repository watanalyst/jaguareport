<?php

/**
 * Configuração dos tipos de documento de exportação.
 *
 * Cada chave é o código do documento (PF, BL, CKL, etc.).
 * - label:         Nome legível do documento
 * - copy_original: true = mostra modal Cópia/Original antes de gerar PDF
 * - view:          Template Blade para geração do PDF
 * - orientation:   Orientação do PDF (portrait ou landscape)
 *
 * Ordem segue a grid do Scriptcase.
 */

return [

    'PF' => [
        'label'         => 'Proforma',
        'copy_original' => false,
        'view'          => 'reports.pdf.exportacao.proforma',
        'orientation'   => 'portrait',
    ],
    'BL' => [
        'label'         => 'Instrução BL',
        'copy_original' => false,
        'view'          => 'reports.pdf.exportacao.instrucao-bl',
        'orientation'   => 'portrait',
    ],
    'CKL' => [
        'label'         => 'Check List',
        'copy_original' => false,
        'view'          => 'reports.pdf.exportacao.check-list',
        'orientation'   => 'portrait',
    ],
    'IN' => [
        'label'         => 'Invoice',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.invoice',
        'orientation'   => 'portrait',
    ],
    'INF' => [
        'label'         => 'Invoice Frete',
        'short_label'   => 'IN+F',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.invoice-frete',
        'orientation'   => 'portrait',
    ],
    'FIN' => [
        'label'         => 'Fatura Invoice',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.fatura-invoice',
        'orientation'   => 'portrait',
    ],
    'PL' => [
        'label'         => 'Packing List',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.packing-list',
        'orientation'   => 'portrait',
    ],
    'DC' => [
        'label'         => 'Dioxin Certificate',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.dioxin-cert',
        'orientation'   => 'portrait',
    ],
    'CR' => [
        'label'         => 'Certificate Radioactivity',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.certificate-radioactivity',
        'orientation'   => 'portrait',
    ],
    'SC' => [
        'label'         => 'Salmonella Certificate',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.salmonella-cert',
        'orientation'   => 'portrait',
    ],
    'DPM' => [
        'label'         => 'Declaration Paking Material',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.declaration-paking-material',
        'orientation'   => 'portrait',
    ],
    'ISF' => [
        'label'         => 'ISF',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.isf',
        'orientation'   => 'portrait',
    ],
    'PRO' => [
        'label'         => 'Protocolo',
        'copy_original' => false,
        'view'          => 'reports.pdf.exportacao.protocolo',
        'orientation'   => 'portrait',
    ],
    'DEC' => [
        'label'         => 'Declaração',
        'copy_original' => false,
        'view'          => 'reports.pdf.exportacao.declaracao',
        'orientation'   => 'portrait',
    ],
    'HFC' => [
        'label'         => 'Hormon Certificate',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.hormon-cert',
        'orientation'   => 'portrait',
    ],
    'MFC' => [
        'label'         => 'MBM Certificate',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.mbm-cert',
        'orientation'   => 'portrait',
    ],
    'FC' => [
        'label'         => 'Feed Certificate',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.feed-cert',
        'orientation'   => 'portrait',
    ],
    'NDFC' => [
        'label'         => 'Newcastle Disease Certificate',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.newcastle-disease-cert',
        'orientation'   => 'portrait',
    ],
    'CONG' => [
        'label'         => 'Certificate of Non GMO',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.certificate-non-gmo',
        'orientation'   => 'portrait',
    ],
    'QC' => [
        'label'         => 'Quality Certificate',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.quality-cert',
        'orientation'   => 'portrait',
    ],
    'AFC' => [
        'label'         => 'Antibiotics Certificate',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.antibiotics-cert',
        'orientation'   => 'portrait',
    ],
    'WC' => [
        'label'         => 'Weight Certificate',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.weight-cert',
        'orientation'   => 'portrait',
    ],
    'EALNC' => [
        'label'         => 'Expiry and Lot Number Certificate',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.expiry-lot-number-cert',
        'orientation'   => 'portrait',
    ],
    'VGM' => [
        'label'         => 'VGM',
        'copy_original' => true,
        'view'          => 'reports.pdf.exportacao.vgm',
        'orientation'   => 'portrait',
    ],
];

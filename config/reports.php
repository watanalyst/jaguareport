<?php

return [

    'financeiro' => [
        'label' => 'Financeiro',

        'contas_receber' => [
            'label'    => 'Contas a Receber',
            'children' => [

                'comissao' => [
                    'label'    => 'Comissão',
                    'app_name' => 'blank_COMISSAO',
                    'route'    => 'relatorios.financeiro.comissao',
                    'filters' => [
                        ['name' => 'ep',         'label' => 'Empresa',                'type' => 'select', 'required' => false],
                        ['name' => 'repres_ini', 'label' => 'Representante Inicial',  'type' => 'text',   'required' => true],
                        ['name' => 'repres_fim', 'label' => 'Representante Final',    'type' => 'text',   'required' => true],
                        ['name' => 'televendas', 'label' => 'Televendas',             'type' => 'radio',  'required' => false, 'options' => ['' => 'Todos', 'S' => 'Sim', 'N' => 'Não']],
                        ['name' => 'clt',        'label' => 'CLT',                    'type' => 'radio',  'required' => false, 'options' => ['' => 'Todos', 'S' => 'Sim', 'N' => 'Não']],
                        ['name' => 'fr',         'label' => 'Tipo Frete',             'type' => 'radio',  'required' => false, 'options' => ['' => 'Ambos', 'C' => 'CIF', 'F' => 'FOB']],
                        ['name' => 'data_ini',   'label' => 'Data Crédito Inicial',   'type' => 'date',   'required' => false],
                        ['name' => 'data_fim',   'label' => 'Data Crédito Final',     'type' => 'date',   'required' => false],
                    ],
                ],

                'comissao_redeconomia' => [
                    'label'    => 'Comissão Redeconomia',
                    'app_name' => 'blank_COMISSAO_REDECONOMIA',
                    'route'    => 'relatorios.financeiro.comissao_redeconomia',
                    'filters' => [
                        ['name' => 'data_ini', 'label' => 'Data Início', 'type' => 'date', 'required' => true],
                        ['name' => 'data_fim', 'label' => 'Data Fim',    'type' => 'date', 'required' => true],
                    ],
                ],

                'comissao_representante' => [
                    'label'    => 'Comissão Representante',
                    'app_name' => 'blank_COMISSAO_REPRESENTANTE',
                    'route'    => 'relatorios.financeiro.comissao_representante',
                    'grid'     => true,
                    'filters'  => [
                        ['name' => 'emp',        'label' => 'Empresa',        'type' => 'select',  'required' => false],
                        ['name' => 'data_ini',   'label' => 'Data Início',    'type' => 'date',    'required' => false],
                        ['name' => 'data_fim',   'label' => 'Data Fim',       'type' => 'date',    'required' => false],
                        ['name' => 'cod_repres', 'label' => 'Representante',  'type' => 'text',    'required' => false, 'placeholder' => 'Ex: 3, 5, 59'],
                        ['name' => 'status',     'label' => 'Status',           'type' => 'select',  'required' => false,
                         'options' => ['' => 'Todos', 'S' => 'Aprovado', 'N' => 'Pendente']],
                    ],
                ],

                'fechamento_cambio' => [
                    'label'    => 'Fechamento Câmbio',
                    'app_name' => 'blank_FECHAMENTO_CAMBIO',
                    'route'    => 'relatorios.financeiro.fechamento_cambio',
                    'csv'      => true,
                    'filters'  => [
                        ['name' => 'dat_cambio',     'label' => 'Data Câmbio',  'type' => 'date',   'required' => true],
                        ['name' => 'cod_banco',      'label' => 'Banco',        'type' => 'select', 'required' => true, 'placeholder' => 'Selecione'],
                        ['name' => 'fech',           'label' => 'Fech',         'type' => 'select', 'required' => false, 'placeholder' => 'Selecione',
                         'options' => ['' => 'Selecione', 'D0' => 'D+0', 'D1' => 'D+1', 'D2' => 'D+2', 'ACC' => 'ACC', 'TRV' => 'TRV', 'TRF' => 'TRF', 'FIN' => 'FIN', 'PGTO' => 'PGTO']],
                        ['name' => 'ies_due',        'label' => 'DUE',          'type' => 'radio',  'required' => false,
                         'options' => ['' => 'Todos', 'S' => 'Sim', 'N' => 'Não']],
                        ['name' => 'num_seq_cambio', 'label' => 'Seq. Câmbio',  'type' => 'text',   'required' => false],
                        ['name' => 'num_contrato',   'label' => 'Nº Contrato',  'type' => 'text',   'required' => false],
                    ],
                ],

            ],
        ],
    ],

    'exportacao' => [
        'label' => 'Exportação',

        'processo' => [
            'label'    => 'Processo',
            'children' => [

                'embarques_exportacao' => [
                    'label'    => 'Embarques Exportação',
                    'app_name' => 'blank_EMBARQUES_EXPORTACAO',
                    'route'    => 'relatorios.exportacao.embarques_exportacao',
                    'grid'     => true,
                    'filters'  => [
                        ['name' => 'empresa',      'label' => 'Empresa',          'type' => 'dual-select', 'required' => true],
                        ['name' => 'dt_prev_ini',  'label' => 'Prev. VDJ Início', 'type' => 'date',     'required' => false],
                        ['name' => 'dt_prev_fim',  'label' => 'Prev. VDJ Fim',    'type' => 'date',     'required' => false],
                        ['name' => 'situacao_ped', 'label' => 'Situação',         'type' => 'select',   'required' => false,
                         'options' => ['' => 'Todas', 'ABERTO' => 'Aberto', 'C/ BOOKING' => 'C/ Booking', 'FATURADO' => 'Faturado', 'PEDIDO GERADO' => 'Pedido Gerado']],
                        ['name' => 'cod_item',     'label' => 'Cód Item',         'type' => 'combobox', 'required' => false, 'placeholder' => 'Pesquisar item...'],
                    ],
                ],

                'processos_exportacao' => [
                    'label'    => 'Processos Exportação',
                    'app_name' => 'blank_PROCESSOS_EXPORTACAO',
                    'route'    => 'relatorios.exportacao.processos_exportacao',
                    'grid'     => true,
                    'filters'  => [
                        ['name' => 'empresa',      'label' => 'Empresa',       'type' => 'select',  'required' => true],
                        ['name' => 'num_processo',  'label' => 'Num Processo',  'type' => 'text',    'required' => true, 'placeholder' => 'Ex: 123, 456, 789'],
                        ['name' => 'ano_processo',  'label' => 'Ano Processo',  'type' => 'text',    'required' => true],
                        ['name' => 'dat_inclusao',  'label' => 'Data Inclusão', 'type' => 'date',    'required' => false],
                        ['name' => 'cod_situacao',  'label' => 'Situação',      'type' => 'select',  'required' => false,
                         'options' => ['' => 'Todas', 'A' => 'Aberto', 'C' => 'Cancelado', 'F' => 'Faturado', 'O' => 'Outros', 'P' => 'Pedido Gerado']],
                    ],
                ],

                'packing_list' => [
                    'label'    => 'Packing List',
                    'app_name' => 'blank_FORM_PACKING_LIST',
                    'route'    => 'relatorios.exportacao.packing_list',
                    'crud'     => true,
                ],

            ],
        ],

        'financeiro_exp' => [
            'label'    => 'Financeiro',
            'children' => [

                'comissao_exportacao' => [
                    'label'    => 'Comissão Exportação',
                    'app_name' => 'blank_COMISSAO_EXPORTACAO',
                    'route'    => 'relatorios.exportacao.comissao_exportacao',
                    'grid'     => true,
                    'filters'  => [
                        ['name' => 'nom_agente',        'label' => 'Agente',              'type' => 'combobox', 'required' => false, 'placeholder' => 'Pesquisar agente...'],
                        ['name' => 'dat_confirm_ini',   'label' => 'Dt Confirm. Pgto Início', 'type' => 'date', 'required' => false],
                        ['name' => 'dat_confirm_fim',   'label' => 'Dt Confirm. Pgto Fim',    'type' => 'date', 'required' => false],
                    ],
                ],

                'credit_note' => [
                    'label'    => 'Credit Note',
                    'app_name' => 'blank_CREDIT_NOTE',
                    'route'    => 'relatorios.exportacao.credit_note',
                    'filters'  => [
                        ['name' => 'cod_empresa',  'label' => 'Empresa',       'type' => 'select',  'required' => true],
                        ['name' => 'num_nc',       'label' => 'Nº NC',         'type' => 'text',    'required' => true, 'placeholder' => 'Ex: 0001'],
                        ['name' => 'ano_nc',       'label' => 'Ano NC',        'type' => 'text',    'required' => true, 'placeholder' => 'Ex: 2026'],
                        ['name' => 'dados_banco',  'label' => 'Dados Banco',   'type' => 'radio',   'required' => true,
                         'options' => ['S' => 'Sim', 'N' => 'Não']],
                    ],
                ],

                'debit_note' => [
                    'label'    => 'Debit Note',
                    'app_name' => 'blank_DEBIT_NOTE',
                    'route'    => 'relatorios.exportacao.debit_note',
                    'filters'  => [
                        ['name' => 'cod_empresa',  'label' => 'Empresa',       'type' => 'select',  'required' => true],
                        ['name' => 'num_nd',       'label' => 'Nº ND',         'type' => 'text',    'required' => true, 'placeholder' => 'Ex: 01'],
                        ['name' => 'ano_nd',       'label' => 'Ano ND',        'type' => 'text',    'required' => true, 'placeholder' => 'Ex: 2026'],
                    ],
                ],

                'baixa_disponivel' => [
                    'label'    => 'Baixa Disponível',
                    'app_name' => 'blank_BAIXA_DISPONIVEL',
                    'route'    => 'relatorios.exportacao.baixa_disponivel',
                    'grid'     => true,
                    'filters'  => [
                        ['name' => 'dat_conf_pgto_ini', 'label' => 'Dt Conf. Pgto Início', 'type' => 'date', 'required' => true],
                        ['name' => 'dat_conf_pgto_fim', 'label' => 'Dt Conf. Pgto Fim',    'type' => 'date', 'required' => true],
                    ],
                ],

                'banco_credit_note' => [
                    'label'    => 'Banco Credit Note',
                    'app_name' => 'blank_BANCO_CREDITO_NOTE',
                    'route'    => 'relatorios.exportacao.banco_credit_note',
                    'crud'     => true,
                ],

                'cambio_periodo' => [
                    'label'    => 'Câmbio Período',
                    'app_name' => 'blank_CAMBIO_PERIODO',
                    'route'    => 'relatorios.exportacao.cambio_periodo',
                    'grid'     => true,
                    'filters'  => [
                        ['name' => 'cod_empresa',    'label' => 'Empresa',           'type' => 'select',  'required' => false],
                        ['name' => 'dat_cambio_ini', 'label' => 'Dt Câmbio Início',  'type' => 'date',    'required' => false],
                        ['name' => 'dat_cambio_fim', 'label' => 'Dt Câmbio Fim',     'type' => 'date',    'required' => false],
                        ['name' => 'num_processo',   'label' => 'Num Processo',       'type' => 'text',    'required' => false],
                        ['name' => 'ano_processo',   'label' => 'Ano Processo',       'type' => 'text',    'required' => false],
                        ['name' => 'embarque',       'label' => 'Embarque',           'type' => 'text',    'required' => false, 'placeholder' => 'Ex: A, B, C'],
                    ],
                ],

                'notas_fiscais_exportacao' => [
                    'label'    => 'Notas Fiscais Exportação',
                    'app_name' => 'blank_NOTAS_FISCAIS_EXPORTACAO',
                    'route'    => 'relatorios.exportacao.notas_fiscais_exportacao',
                    'grid'     => true,
                    'filters'  => [
                        ['name' => 'cod_empresa',      'label' => 'Empresa',           'type' => 'select',  'required' => false],
                        ['name' => 'dat_emissao_ini',  'label' => 'Dt Emissão Início', 'type' => 'date',    'required' => false],
                        ['name' => 'dat_emissao_fim',  'label' => 'Dt Emissão Fim',    'type' => 'date',    'required' => false],
                        ['name' => 'num_processo',     'label' => 'Num Processo',       'type' => 'text',    'required' => false],
                        ['name' => 'ano_processo',     'label' => 'Ano Processo',       'type' => 'text',    'required' => false],
                        ['name' => 'embarque',         'label' => 'Embarque',           'type' => 'text',    'required' => false, 'placeholder' => 'Ex: A, B, C'],
                    ],
                ],

            ],
        ],
    ],

    'logistica' => [
        'label' => 'Logística',
    ],

    'suprimentos' => [
        'label' => 'Suprimentos',
    ],

    'administrativo' => [
        'label' => 'Administrativo',
    ],

    'contabilidade' => [
        'label' => 'Contabilidade',
    ],

    'custos' => [
        'label' => 'Custos',
    ],

    'pcp' => [
        'label' => 'PCP',
    ],

    'esg' => [
        'label' => 'ESG',
    ],

    'ti' => [
        'label' => 'TI',
    ],

    'mercado_interno' => [
        'label' => 'Mercado Interno',
    ],

    'mercado_externo' => [
        'label' => 'Mercado Externo',
    ],

];

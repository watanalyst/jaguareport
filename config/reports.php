<?php

return [

    'financeiro' => [
        'label' => 'Financeiro',

        'comissao' => [
            'label'  => 'Comissão',
            'codigo' => 'FIN-001',
            'route'  => 'relatorios.financeiro.comissao',
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
            'label'  => 'Comissão Redeconomia',
            'codigo' => 'FIN-002',
            'route'  => 'relatorios.financeiro.comissao_redeconomia',
            'filters' => [
                ['name' => 'data_ini', 'label' => 'Data Início', 'type' => 'date', 'required' => true],
                ['name' => 'data_fim', 'label' => 'Data Fim',    'type' => 'date', 'required' => true],
            ],
        ],
    ],

];

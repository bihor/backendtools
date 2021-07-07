<?php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session',
        'label' => 'action',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'enablecolumns' => [
        ],
        'searchFields' => 'action',
        'iconfile' => 'EXT:backendtools/Resources/Public/Icons/tx_backendtools_domain_model_session.gif'
    ],
    'types' => [
        '1' => ['showitem' => 'action, value1, value2, value3, value4, value5, value6, pageel, pagestart, beuser'],
    ],
    'columns' => [

        'action' => [
            'exclude' => false,
            'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.action',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'value1' => [
            'exclude' => false,
            'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.value1',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'int'
            ]
        ],
        'value2' => [
            'exclude' => false,
            'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.value2',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'int'
            ]
        ],
        'value3' => [
            'exclude' => false,
            'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.value3',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'int'
            ]
        ],
        'value4' => [
            'exclude' => false,
            'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.value4',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'value5' => [
            'exclude' => false,
            'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.value5',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'value6' => [
            'exclude' => false,
            'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.value6',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'pageel' => [
            'exclude' => false,
            'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.pageel',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'int'
            ],
        ],
        'pagestart' => [
            'exclude' => false,
            'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.pagestart',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'int'
            ],
        ],
        'beuser' => [
            'exclude' => false,
            'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.beuser',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'be_users',
                'minitems' => 0,
                'maxitems' => 1,
                'appearance' => [
                    'collapseAll' => 0,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ],
            ],
        ],
    
    ],
];

<?php

$LLL = 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:';

return [
    'ctrl' => [
        'title' => 'Notification configuration',
        'label' => 'title',
        'descriptionColumn' => 'rowDescription',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'default_sortby' => 'sorting',
        'rootLevel' => -1,
        'iconfile' => 'EXT:notifications_framework/Resources/Public/Icons/notification-configure.svg',
        'searchFields' => 'title,rowDescription',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'palettes' => [
        'core' => [
            'showitem' => 'title,--linebreak--,type',
        ],
        'hidden' => [
            'showitem' => '
                hidden;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:field.default.hidden
            ',
        ],
    ],
    'types' => [
        [
            'showitem' => '--div--;' . $LLL . 'div.configuration,
                            --palette--;;core,
                            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                            --palette--;;hidden,
                            --palette--;;access,
                            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                            rowDescription,',
        ],
    ],
    'columns' => [
        'pid' => [
            'label' => 'pid',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'crdate' => [
            'label' => 'crdate',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tstamp' => [
            'label' => 'tstamp',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'sorting' => [
            'label' => 'sorting',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'title' => [
            'label' => $LLL . 'configuration.title',
            'config' => [
                'type' => 'input',
                'required' => true,
            ],
        ],
        'rowDescription' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.description',
            'config' => [
                'type' => 'text',
                'rows' => 5,
                'cols' => 30,
            ],
        ],
        'type' => [
            'label' => $LLL . 'configuration.type',
            'description' => $LLL . 'configuration.type.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemGroups' => [
                    'actions' => 'Actions',
                    'status' => 'Status',
                ],
                'items' => [
                    ['label' => '', 'value' => ''],
//                    ['label' => 'Success', 'value' => 'success', 'icon' => 'actions-check-circle','group' => 'status'],
//                    ['label' => 'Warning', 'value' => 'warning', 'icon' => 'actions-exclamation-circle','group' => 'status'],
//                    ['label' => 'Info', 'value' => 'info', 'icon' => 'actions-info-circle','group' => 'status'],
//                    ['label'=> 'Record added', 'value' => 'recordadded', 'icon' => 'actions-plus-circle','group' => 'actions'],
                ],
                'sortItems' => [
                    'label' => 'asc',
                ],
            ],
        ],
    ],
];
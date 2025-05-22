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
        'type' => 'target_audience',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'palettes' => [
        'core' => [
            'showitem' => 'title,push,--linebreak--,type',
        ],
        'message' => [
            'showitem' => 'label,--linebreak--,message',
        ],
        'record' => [
            'showitem' => 'record',
        ],
        'audience' => [
            'showitem' => 'target_audience',
        ],
        'audience_fe' => [
            'showitem' => 'fe_users',
        ],
        'audience_groups' => [
            'showitem' => 'fe_groups',
        ],
        'audience_users' => [
            'showitem' => 'fe_users',
        ],
        'notes' => [
            'showitem' => 'rowDescription,--linebreak--,table',
        ],
        'hidden' => [
            'showitem' => '
                hidden;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:field.default.hidden
            ',
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '--div--;' . $LLL . 'div.configuration,
                --palette--;;core,
                --palette--;;message,
                --palette--;;record,
                --div--;' . $LLL . 'div.audience,
                --palette--;;audience,
                --palette--;;audience_users,
                --palette--;;audience_groups,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;;access,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                --palette--;;notes',
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
        'push' => [
            'label' => $LLL . 'configuration.push',
            'description' => 'If this is true, the configuration will be used to generate notifications',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxLabeledToggle',
                'items' => [
                    [
                        'label' => '',
                        'labelChecked' => 'TRUE',
                        'labelUnchecked' => 'FALSE',
                    ],
                ],
            ],
        ],
        'hidden' => [
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
                    'actions' => $LLL . 'configuration.type.groups.actions',
                    'status' => $LLL . 'configuration.type.groups.status',
                ],
                'items' => [
                    ['label' => $LLL . 'configuration.type.' . \TRAW\NotificationsFramework\Domain\Model\Type::DEFAULT, 'value' => \TRAW\NotificationsFramework\Domain\Model\Type::DEFAULT, 'icon' => 'actions-circle'],
                    ['label' => $LLL . 'configuration.type.' . \TRAW\NotificationsFramework\Domain\Model\Type::SUCCESS, 'value' => \TRAW\NotificationsFramework\Domain\Model\Type::SUCCESS, 'icon' => 'actions-check-circle', 'group' => 'status'],
                    ['label' => $LLL . 'configuration.type.' . \TRAW\NotificationsFramework\Domain\Model\Type::WARNING, 'value' => \TRAW\NotificationsFramework\Domain\Model\Type::WARNING, 'icon' => 'actions-exclamation-circle', 'group' => 'status'],
                    ['label' => $LLL . 'configuration.type.' . \TRAW\NotificationsFramework\Domain\Model\Type::INFO, 'value' => \TRAW\NotificationsFramework\Domain\Model\Type::INFO, 'icon' => 'actions-info-circle', 'group' => 'status'],
                    ['label' => $LLL . 'configuration.type.' . \TRAW\NotificationsFramework\Domain\Model\Type::RECORDADDED, 'value' => \TRAW\NotificationsFramework\Domain\Model\Type::RECORDADDED, 'icon' => 'actions-plus-circle', 'group' => 'actions'],
                    ['label' => $LLL . 'configuration.type.' . \TRAW\NotificationsFramework\Domain\Model\Type::RECORDUPDATED, 'value' => \TRAW\NotificationsFramework\Domain\Model\Type::RECORDUPDATED, 'icon' => 'actions-redo', 'group' => 'actions'],
                ],
                'sortItems' => [
                    'label' => 'asc',
                ],
            ],
        ],
        'label' => [
            'label' => $LLL . 'configuration.label',
            'config' => [
                'type' => 'input',
            ],
        ],
        'message' => [
            'label' => $LLL . 'configuration.message',
            'config' => [
                'type' => 'text',
            ],
        ],
        'table' => [
            'label' => 'Table',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'target_audience' => [
            'label' => $LLL . 'configuration.target_audience',
            'description' => $LLL . 'configuration.target_audience.description',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemGroups' => [
                    'groups' => 'Groups',
                    'users' => 'Users',
                ],
                'items' => [
                    ['label' => '', 'value' => ''],
                    ['label' => 'Mixed', 'value' => 'mixed'],
                    ['label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:fe_users', 'value' => 'users', 'icon' => 'status-user-frontend'],
                    ['label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:fe_groups', 'value' => 'groups', 'icon' => 'status-user-group-frontend'],
                ],
                'required' => true,
            ],
        ],
        'fe_groups' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:fe_groups',
            'description' => $LLL . 'configuration.fe_user_groups.description',
            'displayCond' => 'FIELD:target_audience:IN:mixed,groups',
            'config' => [
                'type' => 'group',
                'allowed' => 'fe_groups',
                'size' => 3,
                'maxitems' => 50,
                'fieldControl' => [
                    'editPopup' => [
                        'disabled' => true,
                    ],
                    'addRecord' => [
                        'disabled' => true,
                    ],
                    'listModule' => [
                        'disabled' => true,
                    ],
                ],
            ],
        ],
        'fe_users' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:fe_users',
            'description' => $LLL . 'configuration.fe_users.description',
            'displayCond' => 'FIELD:target_audience:IN:mixed,users',
            'config' => [
                'type' => 'group',
                'allowed' => 'fe_users',
                'size' => 3,
                'maxitems' => 50,
                'fieldControl' => [
                    'editPopup' => [
                        'disabled' => true,
                    ],
                    'addRecord' => [
                        'disabled' => true,
                    ],
                    'listModule' => [
                        'disabled' => true,
                    ],
                ],
            ],
        ],
        'record' => [
            'label' => $LLL . 'configuration.record',
            'description' => $LLL . 'configuration.record.description',
            'config' => [
                'type' => 'group',
                'allowed' => '*',
                'maxitems' => 1,
                'size' => 1,
            ],
        ],
    ],
];
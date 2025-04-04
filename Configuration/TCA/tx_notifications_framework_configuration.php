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
            'showitem' => 'title,--linebreak--,type,--linebreak--,label,--linebreak--,notification_text',
        ],
        'record' => [
            'showitem' => 'record',
        ],
        'audience' => [
            'showitem' => 'target_audience',
        ],
        'audience_fe' => [
            'showitem' => 'fe_groups,fe_users',
        ],
        'audience_be' => [
            'showitem' => 'be_groups,be_users',
        ],
        'audience_groups' => [
            'showitem' => 'fe_groups,be_groups',
        ],
        'audience_users' => [
            'showitem' => 'fe_users,be_users',
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
                             --div--;' . $LLL . 'div.audience,
                            --palette--;;audience,
                            --palette--;;audience_fe,
                            --palette--;;audience_be,
                            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                            --palette--;;hidden,
                            --palette--;;access,
                            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                            rowDescription,',
        ],
        'groups' => [
            'showitem' => '--div--;' . $LLL . 'div.configuration,
                            --palette--;;core,
                            --div--;' . $LLL . 'div.audience,
                            --palette--;;audience,
                            --palette--;;audience_groups,
                            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                            --palette--;;hidden,
                            --palette--;;access,
                            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                            rowDescription,',
        ],
        'users' => [
            'showitem' => '--div--;' . $LLL . 'div.configuration,
                            --palette--;;core,
                            --div--;' . $LLL . 'div.audience,
                            --palette--;;audience,
                            --palette--;;audience_users,
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
        'push' => [
            'label' => $LLL . 'configuration.push',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    ['label' => '',],
                ],
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
                    ['label' => 'None', 'value' => ''],
                    ['label' => 'Success', 'value' => \TRAW\NotificationsFramework\Domain\Model\Type::SUCCESS, 'icon' => 'actions-check-circle', 'group' => 'status'],
                    ['label' => 'Warning', 'value' => \TRAW\NotificationsFramework\Domain\Model\Type::WARNING, 'icon' => 'actions-exclamation-circle', 'group' => 'status'],
                    ['label' => 'Info', 'value' => \TRAW\NotificationsFramework\Domain\Model\Type::INFO, 'icon' => 'actions-info-circle', 'group' => 'status'],
                    ['label' => 'Record added', 'value' => \TRAW\NotificationsFramework\Domain\Model\Type::RECORDADDED, 'icon' => 'actions-plus-circle', 'group' => 'actions'],
                    ['label' => 'Record updated', 'value' => \TRAW\NotificationsFramework\Domain\Model\Type::RECORDUPDATED, 'icon' => 'actions-plus-circle', 'group' => 'actions'],
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
        'notification_text' => [
            'label' => $LLL . 'configuration.notification_text',
            'config' => [
                'type' => 'text',
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

                    ['label' => 'All user groups', 'value' => 'groups', 'group' => 'groups', 'icon' => 'apps-pagetree-page-backend-users-root'],
                    ['label' => 'Frontend User Groups', 'value' => 'feuser_group', 'group' => 'groups', 'icon' => 'status-user-group-frontend'],
                    ['label' => 'Backend User Groups', 'value' => 'beuser_group', 'group' => 'groups', 'icon' => 'status-user-group-backend'],


                    ['label' => 'All Users', 'value' => 'users', 'group' => 'users', 'icon' => 'apps-pagetree-page-frontend-user-root'],
                    ['label' => 'Frontend Users', 'value' => 'feusers', 'group' => 'users', 'icon' => 'status-user-frontend'],
                    ['label' => 'Backend Users', 'value' => 'beusers', 'group' => 'users', 'icon' => 'status-user-backend'],
                ],
                'required' => true,
            ],
        ],
        'be_groups' => [
            'label' => $LLL . 'configuration.be_user_groups',
            'description' => $LLL . 'configuration.be_user_groups.description',
            'displayCond' => 'FIELD:target_audience:IN:mixed,groups,beuser_group',
            'config' => [
                'type' => 'group',
                'allowed' => 'be_groups',
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
        'be_users' => [
            'label' => $LLL . 'configuration.be_users',
            'description' => $LLL . 'configuration.be_users.description',
            'displayCond' => 'FIELD:target_audience:IN:mixed,users,beusers',
            'config' => [
                'type' => 'group',
                'allowed' => 'be_users',
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
        'fe_groups' => [
            'label' => $LLL . 'configuration.fe_user_groups',
            'description' => $LLL . 'configuration.fe_user_groups.description',
            'displayCond' => 'FIELD:target_audience:IN:mixed,groups,feuser_group',
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
            'label' => $LLL . 'configuration.fe_users',
            'description' => $LLL . 'configuration.fe_users.description',
            'displayCond' => 'FIELD:target_audience:IN:mixed,users,feusers',
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
            'displayCond' => 'FIELD:type:=:record',
            'config' => [
                'type' => 'group',
                'allowed' => '*',
            ],
        ],
    ],
];
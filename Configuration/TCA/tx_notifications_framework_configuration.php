<?php

$LLL = 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:';

$typeClass = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TRAW\NotificationsFramework\Domain\Model\Type::class);
$settingsUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TRAW\NotificationsFramework\Utility\SettingsUtility::class);
$typesWithCustomMessageList = $typeClass->getTypesWithCustomMessageList();
$typesWithRecordFieldList = $typeClass->getTypesWithRecordFieldList();

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
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'translationSource' => 'l10n_source',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'typeicon_classes' => [
            'default' => 'tx-nf-notification-configure',
        ],
    ],
    'palettes' => [
        'core' => [
            'showitem' => 'title,push,done,--linebreak--,type',
        ],
        'message' => [
            'label' => 'Custom message for this notification',
            'showitem' => 'label,--linebreak--,message,--linebreak--,image,--linebreak--,url',
        ],
        'record' => [
            'label' => 'Connected record',
            'showitem' => 'record',
        ],
        'language' => [
            'showitem' => 'sys_language_uid,l10n_parent',
        ],
        'audience' => [
            'label' => 'Target Audience',
            'description' => 'blablabla',
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
            'showitem' => 'rowDescription,--linebreak--,table,--linebreak--,automatic',
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
                preview,
                --div--;' . $LLL . 'div.audience,
                --palette--;;audience,
                --palette--;;audience_users,
                --palette--;;audience_groups,
                 --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                 --palette--;;language,
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
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => '',
                        'value' => 0,
                    ],
                ],
                'foreign_table' => \TRAW\NotificationsFramework\Domain\Model\Configuration::TABLE_NAME,
                // no sys_language_uid = -1 allowed explicitly!
                'foreign_table_where' => 'AND {#' . \TRAW\NotificationsFramework\Domain\Model\Configuration::TABLE_NAME . '}.{#uid}=###CURRENT_PID### AND {#' . \TRAW\NotificationsFramework\Domain\Model\Configuration::TABLE_NAME . '}.{#sys_language_uid} = 0',
                'default' => 0,
            ],
        ],
        'sys_language_uid' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
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
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'done' => [
            'label' => $LLL . 'configuration.done',
            'description' => 'If this is true, the scheduler has finished working on this configuration',
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
                'readOnly' => getenv('TYPO3_CONTEXT') !== 'Development/DDEV',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
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
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
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
            'onChange' => 'reload',
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
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'label' => [
            'label' => $LLL . 'configuration.label',
            'config' => [
                'type' => 'input',
                'max' => 255,
            ],
            'displayCond' => 'FIELD:type:IN:' . $typesWithCustomMessageList,
        ],
        'message' => [
            'label' => $LLL . 'configuration.message',
            'config' => [
                'type' => 'text',
                'max' => 255,
            ],
            'displayCond' => 'FIELD:type:IN:' . $typesWithCustomMessageList,
        ],
        'image' => [
            'label' => $LLL . 'configuration.image',
            'config' => [
                'type' => 'file',
                'maxitems' => 1,
                'allowed' => 'common-image-types',
                'overrideChildTca' => [
                    'columns' => [
                        'title' => false,
                        'link' => false,
                        'description' => false,
                    ],
                ],
            ],
            'displayCond' => 'FIELD:type:IN:' . $typesWithCustomMessageList,
        ],
        'record' => [
            'label' => $LLL . 'configuration.record',
            'description' => $LLL . 'configuration.record.description',
            'config' => [
                'type' => 'group',
                'allowed' => $settingsUtility->getAllowedTablesList(),
                'maxitems' => 1,
                'size' => 1,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
            'displayCond' => 'FIELD:type:IN:' . $typesWithRecordFieldList,
        ],
        'url' => [
            'label' => 'URL',
            'config' => [
                'type' => 'link',
                'size' => 50,
                'appearance' => [
                    'browserTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header_link_formlabel',
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
            'displayCond' => 'FIELD:type:IN:' . $typesWithCustomMessageList,
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
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
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
                'required' => true,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
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
                'required' => true,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'automatic' => [
            'label' => 'Automatically created',
            'config' => [
                'type' => 'check',
                'default' => 0,
                'readOnly' => true,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'table' => [
            'label' => 'Table',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],

    ],
];
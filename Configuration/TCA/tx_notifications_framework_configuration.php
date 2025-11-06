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
            'showitem' => 'title',
        ],
        'settings' => [
            'label' => $LLL . 'palette.settings',
            'showitem' => 'type,push,autotranslate,done',
        ],
        'message' => [
            'label' => $LLL . 'palette.message',
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
            'label' => $LLL . 'palette.audience',
            'description' => $LLL . 'palette.audience.description',
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
                --palette--;;settings,
                notification_estimate,
                --palette--;;message,
                --palette--;;record,
                --div--;' . $LLL . 'div.audience,
                --palette--;;audience,
                --palette--;;audience_users,
                --palette--;;audience_groups,
                 --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                 --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;;access,
                table,
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
            'exclude' => true,
            'label' => $LLL . 'configuration.push',
            'description' => $LLL . 'configuration.push.description',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxLabeledToggle',
                'items' => [
                    [
                        'label' => '',
                        'labelChecked' => 'ENABLED',
                        'labelUnchecked' => 'DISABLED',
                    ],
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'done' => [
            'l10n_mode' => 'exclude',
            'exclude' => true,
            'label' => $LLL . 'configuration.done',
            'description' => $LLL . 'configuration.done.description',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxLabeledToggle',
                'items' => [
                    [
                        'label' => '',
                        'labelChecked' => 'ENABLED',
                        'labelUnchecked' => 'DISABLED',
                    ],
                ],
                'readOnly' => getenv('TYPO3_CONTEXT') !== 'Development/DDEV',
            ],
        ],
        'autotranslate' => [
            'l10n_mode' => 'exclude',
            'exclude' => true,
            'label' => $LLL . 'configuration.autotranslate',
            'description' => $LLL . 'configuration.autotranslate.description',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                    ],
                ],
                'default' => 1,
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
            'exclude' => true,
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
            'exclude' => true,
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
                'default' => \TRAW\NotificationsFramework\Domain\Model\Type::DEFAULT,
                'sortItems' => [
                    'label' => 'asc',
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'label' => [
            'exclude' => true,
            'label' => $LLL . 'configuration.label',
            'config' => [
                'type' => 'input',
                'max' => 255,
            ],
            'displayCond' => 'FIELD:type:IN:' . $typesWithCustomMessageList,
        ],
        'message' => [
            'exclude' => true,
            'label' => $LLL . 'configuration.message',
            'config' => [
                'type' => 'text',
                'max' => 255,
            ],
            'displayCond' => 'FIELD:type:IN:' . $typesWithCustomMessageList,
        ],
        'image' => [
            'exclude' => true,
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
            'exclude' => true,
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
            'exclude' => true,
            'label' => $LLL . 'configuration.url',
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
            'exclude' => true,
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
                    ['label' => $LLL . 'configuration.target_audience.mixed', 'value' => 'mixed'],
                    ['label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:fe_users', 'value' => 'users', 'icon' => 'status-user-frontend'],
                    ['label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:fe_groups', 'value' => 'groups', 'icon' => 'status-user-group-frontend'],
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'fe_groups' => [
            'exclude' => true,
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
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'fe_users' => [
            'exclude' => true,
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
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'automatic' => [
            'exclude' => true,
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
            'exclude' => true,
            'label' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:table',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
                'default' => \TRAW\NotificationsFramework\Domain\Model\Configuration::TABLE_NAME,
            ],
        ],
        'notification_estimate' => [
            'exclude' => true,
            'label' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:notification_estimate',
            'description' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:notification_estimate.description',
            'config' => [
                'type' => 'none',
                'renderType' => 'notificationEstimate',
                'fieldInformation' => [
                    'tcaDescription' => [
                        'renderType' => 'tcaDescription',
                    ],
                ],
            ],
        ],
    ],
];

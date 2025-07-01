<?php

$LLL = 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:';
$typeClass = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TRAW\NotificationsFramework\Domain\Model\Type::class);
$typesWithCustomMessageList = $typeClass->getTypesWithCustomMessageList();
$typesWithRecordFieldList = $typeClass->getTypesWithRecordFieldList();

return [
    'ctrl' => [
        'title' => 'Notification',
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
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'translationSource' => 'l10n_source',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '--div--;' . $LLL . 'div.configuration,
                title,
                fe_user,
                configuration,
                label,
                message,
                read,
                read_date,
                
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
                'foreign_table' => \TRAW\NotificationsFramework\Domain\Model\Notification::TABLE_NAME,
                // no sys_language_uid = -1 allowed explicitly!
                'foreign_table_where' => 'AND {#' . \TRAW\NotificationsFramework\Domain\Model\Notification::TABLE_NAME . '}.{#uid}=###CURRENT_PID### AND {#' . \TRAW\NotificationsFramework\Domain\Model\Notification::TABLE_NAME . '}.{#sys_language_uid} = 0',
                'default' => 0,
            ],
        ],
        'sys_language_uid' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
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
        'configuration' => [
            'label' => 'Configuration',
            'config' => [
                'type' => 'input',
//                'renderType' => 'selectSingle',
//                'default' => 0,
//                'foreign_table' => \TRAW\NotificationsFramework\Domain\Model\Configuration::TABLE_NAME,
            ],

        ],
        'fe_user' => [
            'label' => 'User',
            'config' => [
                'type' => 'input',
//                'renderType' => 'selectSingle',
//                'default' => 0,
//                'foreign_table' => 'fe_users',
            ],
        ],
        'read' => [
            'label' => 'Read',
            'config' => [
                'type' => 'check',
                'readOnly' => true,
                'default' => 0,
            ],
        ],
        'read_date' => [
            'label' => 'Read date',
            'config' => [
                'type' => 'datetime',
                'readOnly' => true,
                'default' => 0,
            ],
        ],
        'type' => [
            'label' => $LLL . 'configuration.type',
            'description' => $LLL . 'configuration.type.description',
            'onChange' => 'reload',
            'config' => [
                'type' => 'input',
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
    ],
];
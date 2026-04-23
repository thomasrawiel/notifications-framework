<?php
declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'Notification Reference',
        'label' => 'fe_user',
        'label_alt' => 'notification',
        'label_alt_force' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'default_sortby' => 'tstamp',
        'rootLevel' => 0,
        'iconfile' => 'EXT:notifications_framework/Resources/Public/Icons/notification-configure.svg',
        'searchFields' => 'title',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'hideTable' => true,
    ],
    'types' => [
        '0' => [
            'showitem' => 'title,user,notification,
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
                'foreign_table' => \TRAW\NotificationsFramework\Domain\Model\Reference::TABLE_NAME,
                // no sys_language_uid = -1 allowed explicitly!
                'foreign_table_where' => 'AND {#' . \TRAW\NotificationsFramework\Domain\Model\Reference::TABLE_NAME . '}.{#uid}=###CURRENT_PID### AND {#' . \TRAW\NotificationsFramework\Domain\Model\Reference::TABLE_NAME . '}.{#sys_language_uid} = 0',
                'default' => 0,
            ],
        ],
        'sys_language_uid' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
            ],
        ],
        'fe_user' => [
            'label' => 'User',
            'config' => [
                'type' => 'select',
                'foreign_table' => \TRAW\NotificationsFramework\Domain\Model\FrontendUser::TABLE_NAME,
                'renderType' => 'selectSingle',
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'notification' => [
            'label' => 'Notification',
            'config' => [
                'type' => 'select',
                'foreign_table' => 'tx_notifications_framework_domain_model_notification',
                'renderType' => 'selectSingle',
                'minitems' => 1,
                'maxitems' => 1,
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
    ],
];

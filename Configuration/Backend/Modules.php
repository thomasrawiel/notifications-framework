<?php

return [
    'notifications' => [
        'parent' => 'web',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/notifications',
        'labels' => [
            'title' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_backend.xlf:module.notifications.title',
            'description' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_backend.xlf:module.notifications.description',
            'shortDescription' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_backend.xlf:module.notifications.shortDescription',
        ],
        'extensionName' => 'NotificationsFramework',
        'iconIdentifier' => 'tx-nf-notification-configure',
        'navigationComponent' => '@typo3/backend/page-tree/page-tree-element',
    ],
    'notifications_index' => [
        'parent' => 'notifications',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/notifications/index',
        'labels' => [
            'title' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_backend.xlf:module.index.title',
        ],
        'extensionName' => 'NotificationsFramework',
        'iconIdentifier' => 'tx-nf-notification-configure',
        'routes' => [
            '_default' => [
                'target' => \TRAW\NotificationsFramework\Controller\Backend\IndexController::class . '::handleRequest',
            ],
        ],
    ],
    'notifications_settings' => [
        'parent' => 'notifications',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/notifications/settings',
        'labels' => [
            'title' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_backend.xlf:module.settings.title',
        ],
        'extensionName' => 'NotificationsFramework',
        'iconIdentifier' => 'tx-nf-notification-configure',
        'routes' => [
            '_default' => [
                'target' => \TRAW\NotificationsFramework\Controller\Backend\SettingsController::class . '::handleRequest',
            ],
        ],
    ],
    'notifications_configurations' => [
        'parent' => 'notifications',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/notifications/configurations',
        'labels' => [
            'title' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_backend.xlf:module.configurations.title',
        ],
        'extensionName' => 'NotificationsFramework',
        'iconIdentifier' => 'tx-nf-notification-configure',
        'moduleData' => [
            'sortField' => 'uid',
            'sortDirection' => 'desc',
            'filter' => [
                'type' => '',
                'valid' => '',
                'status' => '',
            ],
            'currentPage' => 1,
            'perPage' => 30,
        ],
        'routes' => [
            '_default' => [
                'target' => \TRAW\NotificationsFramework\Controller\Backend\ConfigurationsController::class . '::handleRequest',
            ],
            'detail' => [
                'path' => '/detail',
                'target' => \TRAW\NotificationsFramework\Controller\Backend\NotificationsConfigurationsController::class . '::detail',
            ],
        ],
    ],
    'notifications_notifications' => [
        'parent' => 'notifications',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/notifications/notifications',
        'labels' => [
            'title' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_backend.xlf:module.notifications.title',
        ],
        'extensionName' => 'NotificationsFramework',
        'iconIdentifier' => 'tx-nf-notification-configure',
        'moduleData' => [
            'sortField' => 'uid',
            'sortDirection' => 'desc',
//            'filter' => [
//                'type' => '',
//                'valid' => '',
//            ],
            'currentPage' => 1,
            'perPage' => 30,
        ],
        'routes' => [
            '_default' => [
                'target' => \TRAW\NotificationsFramework\Controller\Backend\NotificationsController::class . '::handleRequest',
            ],
            'detail' => [
                'path' => '/detail',
                'target' => \TRAW\NotificationsFramework\Controller\Backend\NotificationsController::class . '::detail',
            ],
        ],
    ],

];

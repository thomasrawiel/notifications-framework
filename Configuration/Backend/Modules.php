<?php

return [
    'notifications' => [
        'parent' => 'web',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/notifications',
        'labels' => [
            'title' => 'LLL:EXT:notifications_framework/Resources/Private/Language/Modules/locallang_notifications_mod.xlf:module.notifications.title',
            'description' => 'LLL:EXT:notifications_framework/Resources/Private/Language/Modules/locallang_notifications_mod.xlf:module.notifications.description',
            'shortDescription' => 'LLL:EXT:notifications_framework/Resources/Private/Language/Modules/locallang_notifications_mod.xlf:module.notifications.shortDescription',
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
            'title' => 'Overview',
        ],
        'extensionName' => 'NotificationsFramework',
        'iconIdentifier' => 'tx-nf-notification-configure',
        'routes' => [
            '_default' => [
                'target' => \TRAW\NotificationsFramework\Controller\Backend\NotificationsIndexController::class . '::handleRequest',
            ],
        ],
    ],
    'notifications_configurations' => [
        'parent' => 'notifications',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/notifications/configurations',
        'labels' => [
            'title' => 'Configurations',
        ],
        'extensionName' => 'NotificationsFramework',
        'iconIdentifier' => 'tx-nf-notification-configure',
        'moduleData' => [
            'sortField' => 'uid',
            'sortDirection' => 'desc',
            'page' => 1,
        ],
        'routes' => [
            '_default' => [
                'target' => \TRAW\NotificationsFramework\Controller\Backend\NotificationsConfigurationsController::class . '::handleRequest',
            ],
            'detail' => [
                'path' => '/detail',
                'target' => \TRAW\NotificationsFramework\Controller\Backend\NotificationsConfigurationsController::class . '::detail',
            ],
        ],
    ],
    'notifications_settings' => [
        'parent' => 'notifications',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/notifications/settings',
        'labels' => [
            'title' => 'Settings',
        ],
        'extensionName' => 'NotificationsFramework',
        'iconIdentifier' => 'tx-nf-notification-configure',
        'routes' => [
            '_default' => [
                'target' => \TRAW\NotificationsFramework\Controller\Backend\SettingsController::class . '::handleRequest',
            ],
        ],
    ],
];

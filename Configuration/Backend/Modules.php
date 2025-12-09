<?php

return [
    'notifications' => [
        'labels' => 'LLL:EXT:notifications_framework/Resources/Private/Language/Modules/locallang_notifications_mod.xlf',
        'iconIdentifier' => 'tx-nf-notification-filled',
        'position' => [
            'after' =>'site',
            'before' => 'tools'
        ]
    ],
    'notification_configurations' => [
        'parent' => 'notifications',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/notifications/configurations',
        'labels' => 'LLL:EXT:notifications_framework/Resources/Private/Language/Modules/locallang_configurations_mod.xlf',
        'extensionName' => 'NotificationFramework',
        'iconIdentifier' => 'tx-nf-notification-configure',
        'controllerActions' => [
            \TRAW\NotificationsFramework\Controller\Backend\ConfigurationsController::class => [
                'listConfigurations',
                'detailConfiguration',
            ],
        ],
        'navigationComponent' => '',
    ],
];

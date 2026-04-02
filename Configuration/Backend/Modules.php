<?php

return [
    'notification_configurations' => [
        'parent' => 'web',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/notifications',
        'labels' => 'LLL:EXT:notifications_framework/Resources/Private/Language/Modules/locallang_notifications_mod.xlf',
        'extensionName' => 'NotificationsFramework',
        'iconIdentifier' => 'tx-nf-notification-configure',
        'controllerActions' => [
            \TRAW\NotificationsFramework\Controller\Backend\NotificationsController::class => [
                'index',
                'listConfigurations',
                'detailConfiguration',
                'listNotifications'
            ],
        ],
        'navigationComponent' => '@typo3/backend/page-tree/page-tree-element',
        'inheritNavigationComponentFromMainModule' => true,
    ],
];

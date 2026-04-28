<?php
declare(strict_types=1);

return [
    'notifications_framework_update_configuration' => [
        'path' => '/notifications-framework/update-configuration-with-suggestion',
        'target' => \TRAW\NotificationsFramework\Controller\Backend\AjaxRoutesController::class . '::updateConfigurationWithSuggestion',
    ],
    'notifications_framework_update_cache' => [
        'path' => '/notifications-framework/update-configuration-cache',
        'target' => \TRAW\NotificationsFramework\Controller\Backend\AjaxRoutesController::class . '::updateConfigurationCache',
    ],
];

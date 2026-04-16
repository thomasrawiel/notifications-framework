<?php

defined('TYPO3') or die('Access denied.');
call_user_func(function ($_EXTKEY = 'notifications_framework') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \TRAW\NotificationsFramework\Hooks\TCEMainHook::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['dispatchEventAfterDatabaseOperations'] ??= true;

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1750162623] = [
        'nodeName' => 'notificationPreview',
        'priority' => 40,
        'class' => \TRAW\NotificationsFramework\Form\NotificationPreview::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1752824456] = [
        'nodeName' => 'notificationEstimate',
        'priority' => 70,
        'class' => \TRAW\NotificationsFramework\Backend\FieldInformation\NotificationEstimate::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1773843897] = [
        'nodeName' => 'configurationValid',
        'priority' => 70,
        'class' => \TRAW\NotificationsFramework\Backend\FieldInformation\ConfigurationValid::class,
    ];


    if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() < 13) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Core\Imaging\IconFactory::class]['overrideIconOverlay'][]
            = \TRAW\NotificationsFramework\Hooks\OverrideIconOverlayHook::class;
    }

    $GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets'][$_EXTKEY]
        = 'EXT:' . $_EXTKEY . '/Resources/Public/Css/backend.css';

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['operationHandlers'] += [
        \TRAW\NotificationsFramework\OperationHandler\GetUserNotificationsOperationHandler::class => 500,
        \TRAW\NotificationsFramework\OperationHandler\PatchUserNotificationsOperationHandler::class => 510,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['notifications_framework_configuration']
        ??= [];
});

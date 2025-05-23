<?php

defined('TYPO3') or die('Access denied.');
call_user_func(function ($_EXTKEY = 'notifications_framework') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \TRAW\NotificationsFramework\Hooks\TCEMainHook::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['dispatchEventAfterDatabaseOperations'] ??= true;

    if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() < 13) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Core\Imaging\IconFactory::class]['overrideIconOverlay'][]
            = \TRAW\NotificationsFramework\Hooks\OverrideIconOverlayHook::class;
    }

    $GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets'][$_EXTKEY]
        = 'EXT:'.$_EXTKEY.'/Resources/Public/Css/backend.css';

});
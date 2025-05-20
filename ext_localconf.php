<?php

defined('TYPO3') or die('Access denied.');
call_user_func(function ($_EXTKEY = 'notifications_framework') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \TRAW\NotificationsFramework\Hooks\TCEMainHook::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['dispatchEventAfterDatabaseOperations'] ??= true;
});
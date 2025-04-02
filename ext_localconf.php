<?php

defined('TYPO3') or die('Access denied.');
call_user_func(function ($_EXTKEY = 'notifications_framework') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php'] =
        array_merge_recursive(
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php'],
            [
                'processDatamapClass' => [
                    $_EXTKEY => \TRAW\NotificationsFramework\Hooks\TCEMainHook::class,
                ],
                'processCmdmapClass' => [
                    $_EXTKEY => \TRAW\NotificationsFramework\Hooks\TCEMainHook::class,
                ],
            ]
        );

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['dispatchEventAfterDatabaseOperations'] ??= true;
});
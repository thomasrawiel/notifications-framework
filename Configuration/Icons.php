<?php

return [
    'tx-nf-notification' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:notifications_framework/Resources/Public/Icons/notification.svg',
    ],
    'tx-nf-notification-filled' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:notifications_framework/Resources/Public/Icons/notification-filled.svg',
    ],
    'tx-nf-notification-configure' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:notifications_framework/Resources/Public/Icons/notification-configure.svg',
    ],
    \TRAW\NotificationsFramework\Utility\RecordIconOverlayUtility::ICON_IDENTIFIER_CHECK => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:notifications_framework/Resources/Public/Icons/overlay-check.svg',
    ],
    \TRAW\NotificationsFramework\Utility\RecordIconOverlayUtility::ICON_IDENTIFIER_QUESTION => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:notifications_framework/Resources/Public/Icons/overlay-question.svg',
    ],
    \TRAW\NotificationsFramework\Utility\RecordIconOverlayUtility::ICON_IDENTIFIER_PAUSE => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:notifications_framework/Resources/Public/Icons/overlay-pause.svg',
    ],
    \TRAW\NotificationsFramework\Utility\RecordIconOverlayUtility::ICON_IDENTIFIER_QUEUE=> [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:notifications_framework/Resources/Public/Icons/overlay-queue.svg',
    ],
];
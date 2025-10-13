<?php

namespace TRAW\NotificationsFramework\Domain\DTO;

final class Notification
{
    public const defaultTCA = [
        'notification_create' => [
            'label' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:notification_create',
            'description' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:notification_create.description',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
            ],
        ],
    ];
}

<?php
declare(strict_types=1);

return [
    \TRAW\NotificationsFramework\Domain\Model\Notification::class => [
        'tableName' => \TRAW\NotificationsFramework\Domain\Model\Notification::TABLE_NAME,
    ],
    \TRAW\NotificationsFramework\Domain\Model\Json\Notification::class => [
        'tableName' => \TRAW\NotificationsFramework\Domain\Model\Notification::TABLE_NAME,
    ],
    \TRAW\NotificationsFramework\Domain\Model\Configuration::class => [
        'tableName' => \TRAW\NotificationsFramework\Domain\Model\Configuration::TABLE_NAME,
    ],
    \TRAW\NotificationsFramework\Domain\Model\FrontendUser::class => [
        'tableName' => \TRAW\NotificationsFramework\Domain\Model\FrontendUser::TABLE_NAME,
    ],
    \TRAW\NotificationsFramework\Domain\Model\FileReference::class => [
        'tableName' => 'sys_file_reference',
    ]
];
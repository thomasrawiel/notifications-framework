<?php

namespace TRAW\NotificationsFramework\Domain\Factory;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\FrontendUser;
use TRAW\NotificationsFramework\Domain\Model\Notification;

class NotificationFactory
{
    public static function createNotification(Configuration $configuration, FrontendUser $frontendUser): Notification
    {
        return new Notification($frontendUser->getUid(), $configuration);
    }
}
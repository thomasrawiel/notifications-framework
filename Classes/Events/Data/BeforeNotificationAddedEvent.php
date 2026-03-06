<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Data;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\FrontendUser;
use TRAW\NotificationsFramework\Domain\Model\Notification;

final class BeforeNotificationAddedEvent
{
    public function __construct(private Notification $notification, private Configuration $configuration)
    {
    }

    public function getNotification(): Notification
    {
        return $this->notification;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function setNotification(Notification $notification): void
    {
        $this->notification = $notification;
    }
}

<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Data;

use TRAW\NotificationsFramework\Domain\Model\Notification;

final class BeforeNotificationAddedEvent
{
    public bool $addNotification = true;

    public function __construct(private Notification $notification)
    {
    }

    public function isAddNotification(): bool
    {
        return $this->addNotification;
    }

    public function getNotification(): Notification
    {
        return $this->notification;
    }

    public function setAddNotification(bool $addNotification): void
    {
        $this->addNotification = $addNotification;
    }

    public function setNotification(Notification $notification): void
    {
        $this->notification = $notification;
    }
}
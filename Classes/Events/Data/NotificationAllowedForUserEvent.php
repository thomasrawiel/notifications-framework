<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Data;

use TRAW\NotificationsFramework\Domain\Model\FrontendUser;
use TRAW\NotificationsFramework\Domain\Model\Notification;

class NotificationAllowedForUserEvent
{
    public function __construct(private Notification $notification, private FrontendUser $frontendUser, private bool $isAllowed = true){}

    public function getNotification(): Notification
    {
        return $this->notification;
    }

    public function getFrontendUser(): FrontendUser
    {
        return $this->frontendUser;
    }

    public function isAllowed(): bool
    {
        return $this->isAllowed;
    }

    public function setIsAllowed(bool $isAllowed): void
    {
        $this->isAllowed = $isAllowed;
    }
}

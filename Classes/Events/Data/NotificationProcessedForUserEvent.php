<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Data;

use TRAW\NotificationsFramework\Domain\Model\FrontendUser;
use TRAW\NotificationsFramework\Domain\Model\Notification;

class NotificationProcessedForUserEvent
{

    public function __construct(private Notification $notification, private FrontendUser $frontendUser){}

    public function getNotification(): Notification
    {
        return $this->notification;
    }

    public function getFrontendUser(): FrontendUser
    {
        return $this->frontendUser;
    }
}

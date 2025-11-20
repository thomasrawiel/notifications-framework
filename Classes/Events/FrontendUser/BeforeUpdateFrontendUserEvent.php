<?php

namespace TRAW\NotificationsFramework\Events\FrontendUser;

final class BeforeUpdateFrontendUserEvent extends AbstractFrontendUserEvent
{
    protected array $notifications = [];

    public function __construct(AbstractEntity $frontendUser, $notifications = [])
    {
        parent::__construct($frontendUser);
        if (empty($notifications)) {
            $this->notifications = $this->convertNotificationBitMaskToArray($frontendUser->getNotifications());
        } else {
            $this->frontendUser->setNotifications($this->convertNotificationArrayToBitMask($notifications));
        }
    }

    public function getNotifications(): array
    {
        return $this->notifications;
    }

    public function setNotifications(array $notifications): void
    {
        $this->notifications = $notifications;
    }
}

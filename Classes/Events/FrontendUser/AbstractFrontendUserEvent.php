<?php

namespace TRAW\NotificationsFramework\Events\FrontendUser;

use TRAW\NotificationsFramework\Utility\BitmaskUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

abstract class AbstractFrontendUserEvent
{
    protected \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $frontendUser;

    protected array $notifications = [];

    public function __construct(AbstractEntity $frontendUser, $notifications = [])
    {
        $this->frontendUser = $frontendUser;
        if(empty($notifications)) {
            $this->notifications = $this->convertNotificationBitMaskToArray($frontendUser->getNotifications());
        }else {
            $this->frontendUser->setNotifications($this->convertNotificationArrayToBitMask($notifications));
        }
    }

    public function getFrontendUser(): AbstractEntity
    {
        return $this->frontendUser;
    }

    public function setFrontendUser(AbstractEntity $frontendUser): void
    {
        $this->frontendUser = $frontendUser;
    }

    public function getNotifications(): array
    {
        return $this->notifications;
    }

    private function convertNotificationArrayToBitMask(array $notifications): int {
        return BitmaskUtility::encodeCheckboxes($notifications);
    }

    private function convertNotificationBitMaskToArray(int $notifications): array {
        return BitmaskUtility::decodeCheckboxes($notifications, 5);
    }
}
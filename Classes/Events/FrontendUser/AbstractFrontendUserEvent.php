<?php

namespace TRAW\NotificationsFramework\Events\FrontendUser;

use TRAW\NotificationsFramework\Utility\BitmaskUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

abstract class AbstractFrontendUserEvent
{
    protected AbstractEntity $frontendUser;

    public function __construct(AbstractEntity $frontendUser, $notifications = [])
    {
        $this->frontendUser = $frontendUser;
    }

    public function getFrontendUser(): AbstractEntity
    {
        return $this->frontendUser;
    }

    public function setFrontendUser(AbstractEntity $frontendUser): void
    {
        $this->frontendUser = $frontendUser;
    }
}

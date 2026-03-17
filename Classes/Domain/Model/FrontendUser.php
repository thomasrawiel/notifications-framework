<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class FrontendUser
 */
class FrontendUser extends AbstractEntity
{
    /**
     *
     */
    public const TABLE_NAME = 'fe_users';

    /**
     * @var ObjectStorage<Notification>
     */
    protected ?ObjectStorage $notifications = null;

    public function __construct() {
        $this->notifications = new ObjectStorage();
    }

    public function getUserNotifications(): ObjectStorage
    {
        return $this->userNotifications;
    }

    public function addUserNotification(Notification $userNotification): void
    {
        $this->userNotifications->attach($userNotification);
    }

    public function removeUserNotification(Notification $userNotification): void
    {
        $this->userNotifications->detach($userNotification);
    }

    public function setNotifications(?ObjectStorage $notifications): void
    {
        $this->notifications = $notifications;
    }
}

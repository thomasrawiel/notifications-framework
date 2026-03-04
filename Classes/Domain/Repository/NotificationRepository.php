<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Repository;

use TRAW\NotificationsFramework\Domain\Model\Notification;
use TYPO3\CMS\Extbase\Persistence\Repository;

class NotificationRepository extends Repository
{
    public function notificationExists(Notification $notification): bool
    {
        $query = $this->createQuery();

        $constraints = [
            $query->equals('configuration', $notification->getConfiguration()),
            $query->equals('sys_language_uid', $notification->getSysLanguageUid())
        ];

        $query->matching(
            $query->logicalAnd(...$constraints),
        );
        return $query->count() > 0;
    }
}

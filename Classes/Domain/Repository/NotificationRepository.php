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
        $query->matching(
            $query->logicalAnd(
                $query->equals('feUser', $notification->getFeUser()),
                $query->equals('configuration', $notification->getConfiguration()),

            ),
        );
        return $query->count() > 0;
    }
}
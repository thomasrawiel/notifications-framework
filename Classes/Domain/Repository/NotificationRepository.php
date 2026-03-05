<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Repository;

use TRAW\NotificationsFramework\Domain\Model\Notification;
use TYPO3\CMS\Extbase\Persistence\Repository;

class NotificationRepository extends Repository
{
    public function notificationExists(int $configuration): bool
    {
        $query = $this->createQuery();

        return $query->matching(
                $query->equals('configuration', $configuration),
            )->count() > 0;
    }
}

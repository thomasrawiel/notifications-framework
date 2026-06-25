<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Repository;

use TRAW\NotificationsFramework\Domain\Model\Notification;

final class NotificationRepository extends AbstractDemandRepository
{
    public function notificationExists(int $configuration): bool
    {
        $query = $this->createQuery();

        return $query->matching(
                $query->equals('configuration', $configuration),
            )->count() > 0;
    }

    public function getNotification(int $uid): array
    {
        return $this->getNotificationsByDemand(['uid' => $uid]);
    }

    public function getNotificationsByDemand(array $demand = []): array
    {
        return $this->getByDemand(Notification::TABLE_NAME, $demand);
    }
}

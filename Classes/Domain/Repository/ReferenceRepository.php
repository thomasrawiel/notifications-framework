<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Repository;

use TRAW\NotificationsFramework\Domain\Model\Reference;

final class ReferenceRepository extends AbstractDemandRepository
{
    public function referenceExists(int $notification, int $frontendUser): bool
    {
        $query = $this->createQuery();

        return $query->matching(
                $query->logicalAnd(
                    $query->equals('notification', $notification),
                    $query->equals('fe_user', $frontendUser),
                )
            )->count() > 0;
    }

    public function getReferencesByDemand(array $demand = []): array
    {
        return $this->getByDemand(Reference::TABLE_NAME, $demand);
    }
}

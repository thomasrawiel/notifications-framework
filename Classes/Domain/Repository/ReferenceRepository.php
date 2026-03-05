<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

class ReferenceRepository extends Repository
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
}

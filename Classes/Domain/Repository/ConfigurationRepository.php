<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Repository;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TYPO3\CMS\Extbase\Persistence\Repository;

class ConfigurationRepository extends Repository
{
    public function findAll()
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('push', '1'),
                $query->equals('done', '0'),
                $query->in('target_audience', Configuration::AUDIENCE)
            )
        );
        return $query->execute();
    }
}
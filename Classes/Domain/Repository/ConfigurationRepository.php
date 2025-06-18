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

    public function getTranslations($configuration) {
        $query = $this->createQuery();
        $query->setQuerySettings(
            $query->getQuerySettings()->setRespectSysLanguage(false),
        );
        $query->matching(
            $query->logicalAnd(
                $query->greaterThan('sys_language_uid', 0),
                $query->equals('l10n_parent', $configuration->getUid()),
            )
        );
        return $query->execute();
    }
}
<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Repository;

use TRAW\NotificationsFramework\Domain\Model\Configuration;

final class ConfigurationRepository extends AbstractDemandRepository
{
    public function findAll()
    {
        $query = $this->createQuery();
        $query->setQuerySettings(
            $query->getQuerySettings()->setRespectStoragePage(false),

        );

        $targetAudiences = array_merge(Configuration::AUDIENCE, Configuration::PLACEHOLDER_AUDIENCES);
        $query->matching(
            $query->logicalAnd(
                $query->equals('push', 1),
                $query->equals('done', 0),
                $query->in('target_audience', $targetAudiences)
            )
        );
        return $query->execute();
    }

    public function getTranslations(int $configurationUid)
    {
        $query = $this->createQuery();
        $query->setQuerySettings(
            $query->getQuerySettings()->setRespectStoragePage(false),
            $query->getQuerySettings()->setRespectSysLanguage(false),

        );
        $query->matching(
            $query->logicalAnd(
                $query->greaterThan('sys_language_uid', 0),
                $query->equals('l10n_parent', $configurationUid),
            )
        );
        return $query->execute();
    }


    public function getConfiguration(int $configurationUid): array
    {
        return $this->getConfigurationsByDemand(['uid' => $configurationUid]);
    }

    public function getConfigurationsByDemand(array $demand = []): array
    {
        return $this->getByDemand(Configuration::TABLE_NAME, $demand);
    }
}

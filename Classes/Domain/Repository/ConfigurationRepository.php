<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Repository;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

class ConfigurationRepository extends Repository
{
    public function findAll()
    {
        $query = $this->createQuery();

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

    public function getTranslations($configuration)
    {
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

    /**
     * BACKEND MODULES
     */
    public function listConfigurations(array $demand): array
    {
        $widgets = $this->getConfigurations($demand);

        return $this->sortList($widgets, $demand);
    }

    private function getConfigurations(array $demand): array
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_notifications_framework_configuration');

        $sortField = $demand['sortField'] ?? 'uid';
        if (!isset($GLOBALS['TCA']['tx_notifications_framework_configuration']['columns'][$sortField])) {
            $sortField = $GLOBALS['TCA']['tx_notifications_framework_configuration']['ctrl']['sorting'] ?? 'uid';
        }

        $sortDirection = $demand['sortDirection'] ?? 'ASC';

        $qb->select('*')
            ->from('tx_notifications_framework_configuration')
            ->where($qb->expr()->eq('sys_language_uid', $qb->createNamedParameter(0)));

        if ($demand['uid'] ?? false && MathUtility::canBeInterpretedAsInteger($demand['uid'])) {
            $qb->andWhere($qb->expr()->eq('uid', $qb->createNamedParameter($demand['uid'])));
        }

        $qb->orderBy($sortField, $sortDirection);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param array $configurations
     * @param array $demand
     *
     * @return array
     */
    private function sortList(array $configurations, array $demand): array
    {
        $sortableFields = ['uid'];
        $field = $demand['sortField'];

        if (in_array($field, $sortableFields, true)) {
            $modifier = $demand['sortDirection'] === 'asc' ? 1 : -1;
            usort($configurations, function ($a, $b) use ($field, $modifier) {
                $aValue = $a['computed_stats'][$field] ?? null;
                $bValue = $b['computed_stats'][$field] ?? null;

                if ($aValue === $bValue) {
                    return 0;
                }
                if ($aValue === null) return 1;
                if ($bValue === null) return -1;
                return ($aValue < $bValue ? -1 : 1) * $modifier;
            });
        }

        return $configurations;
    }
}

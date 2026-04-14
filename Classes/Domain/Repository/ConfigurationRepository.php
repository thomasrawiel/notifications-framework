<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Repository;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
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
        $configurations = $this->getConfigurations($demand);

        return $this->sortList($configurations, $demand['sortField'], $demand['sortDirection']);
    }

    private function getConfigurations(array $demand): array
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_notifications_framework_configuration');
        $qb->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $sortField = $demand['sortField'] ?? 'uid';
        if (!isset($GLOBALS['TCA']['tx_notifications_framework_configuration']['columns'][$sortField])) {
            $sortField = $GLOBALS['TCA']['tx_notifications_framework_configuration']['ctrl']['sorting'] ?? 'uid';
        }

        $sortDirection = $demand['sortDirection'] ?? 'ASC';

        $constraints = [
            $qb->expr()->eq('sys_language_uid', $qb->createNamedParameter(0)),
            $qb->expr()->eq('pid', $qb->createNamedParameter($demand['pid'])),
        ];
        if ($demand['uid'] ?? false && MathUtility::canBeInterpretedAsInteger($demand['uid'])) {
            $constraints[] = $qb->expr()->eq('uid', $qb->createNamedParameter($demand['uid']));
        }


        $qb->select('*')
            ->from('tx_notifications_framework_configuration')
            ->where(...$constraints);

        $qb->orderBy($sortField, $sortDirection);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param array $configurations
     * @param array $demand
     *
     * @return array
     */
    private function sortList(array $configurations, string $sortField, string $sortDirection = 'asc'): array
    {
        $sortableFields = ['uid'];
        $sortDirection = strtolower($sortDirection);

        if (in_array($sortField, $sortableFields, true)) {
            $modifier = $sortDirection === 'asc' ? 1 : -1;
            usort($configurations, function ($a, $b) use ($sortField, $modifier) {
                $aValue = $a['computed_stats'][$sortField] ?? null;
                $bValue = $b['computed_stats'][$sortField] ?? null;

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

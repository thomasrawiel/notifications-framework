<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
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



    public function getConfiguration(int $configurationUid): array
    {
        return $this->getConfigurationsByDemand(['uid' => $configurationUid]);
    }

    public function getConfigurationsByDemand(array $demand): array
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_notifications_framework_configuration');
        $qb->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $qb->select('*')
            ->from('tx_notifications_framework_configuration');
        if ($demand['uid'] ?? false && MathUtility::canBeInterpretedAsInteger($demand['uid'])) {
            $qb->where($qb->expr()->eq('uid', $qb->createNamedParameter($demand['uid'])));
            return $qb->execute()->fetchAssociative();
        }

        $sortField = $demand['sortField'] ?? 'uid';
        if (!isset($GLOBALS['TCA']['tx_notifications_framework_configuration']['columns'][$sortField])) {
            $sortField = $GLOBALS['TCA']['tx_notifications_framework_configuration']['ctrl']['sorting'] ?? 'uid';
        }

        $sortDirection = $demand['sortDirection'] ?? 'ASC';

        $constraints = [
            $qb->expr()->eq('sys_language_uid', $qb->createNamedParameter(0, ParameterType::INTEGER)),
        ];
        if (is_array($demand['pid'])) {
            $constraints[] = $qb->expr()->in('pid', $qb->createNamedParameter($demand['pid'], ArrayParameterType::INTEGER));
        } else {
            $qb->expr()->eq('pid', $qb->createNamedParameter($demand['pid'], ParameterType::INTEGER));
        }

        $qb->where(...$constraints);
        $qb->orderBy($sortField, $sortDirection);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function sortList(array $configurations, string $sortField, string $sortDirection = 'asc'): array
    {
        $sortDirection = strtolower($sortDirection);

        if (!isset($configurations[0][$sortField])) {
            $sortField = 'uid';
        }

        $modifier = $sortDirection === 'asc' ? 1 : -1;
        usort($configurations, function ($a, $b) use ($sortField, $modifier) {
            $aValue = $a[$sortField] ?? null;
            $bValue = $b[$sortField] ?? null;

            if ($aValue === $bValue) {
                return 0;
            }
            if ($aValue === null) return 1;
            if ($bValue === null) return -1;
            return ($aValue < $bValue ? -1 : 1) * $modifier;
        });


        return $configurations;
    }
}
